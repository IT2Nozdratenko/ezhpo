<?php

namespace App\Console\Commands;

use App\GenerateHashIdTrait;
use App\Repositories\CheckUserRoles\CheckUserRolesRepository;
use App\Services\CheckUserRoles\CheckUserRolesLogsGenerator;
use App\Services\CheckUserRoles\CheckUserRolesRestoreService;
use App\Services\CheckUserRoles\Enums\RestorationDataType;
use App\User;
use Closure;
use DateTimeImmutable;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CheckUserRoles extends Command
{
    use GenerateHashIdTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-roles
                            {--fix : Исправление ошибок}
                            {--c|--companies : Валидация компаний}
                            {--d|--drivers : Валидация водителей}
                            {--u|--users : Валидация пользователей}
                            {--r|--roles : Валидация ролей}
                            {--D|--duplicated-roles : Валидация пользователей с повторяющимися ролями}
                            {--a|--all : Валидация всех типов записей}
                            {--l|--logs-list : Список файлов для восстановления данных}
                            {--restore : Восстановить данные}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка ролей пользователей';

    /**
     * @var boolean
     */
    private $fixNeeded;
    /**
     * @var CheckUserRolesRepository
     */
    private $repository;
    /**
     * @var CheckUserRolesLogsGenerator
     */
    private $logsGenerator;
    /**
     * @var CheckUserRolesRestoreService
     */
    private $restoreService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        CheckUserRolesRepository     $repository,
        CheckUserRolesLogsGenerator  $logsGenerator,
        CheckUserRolesRestoreService $restoreService
    )
    {
        parent::__construct();
        $this->repository = $repository;
        $this->logsGenerator = $logsGenerator;
        $this->restoreService = $restoreService;
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        if ($this->option('logs-list')) {
            $fileNameList = $this->restoreService->getAvailableLogsList();

            if (!count($fileNameList)) {
                $this->info('Файлов для восстановления данных не найдено');
            }

            foreach ($fileNameList as $fileName) {
                $this->info($fileName);
            }

            return;
        }

        $restore = $this->option('restore');
        $this->fixNeeded = !$restore && $this->option('fix');

        $validateAll = $this->option('all');
        $validateDuplicatedRoles = $this->option('duplicated-roles');
        $validateRoles = $this->option('roles');
        $validateCompanies = $this->option('companies');
        $validateDrivers = $this->option('drivers');
        $validateUsers = $this->option('users');

        DB::beginTransaction();

        try {
            if ($restore) {
                $fileNameList = $this->restoreService->getAvailableLogsList();

                if (!count($fileNameList)) {
                    throw new Exception('Не найдено файлов для восстановления');
                }

                $this->info('Доступные файлы:');
                foreach ($fileNameList as $fileName) {
                    $this->info($fileName);
                }

                $value = $this->askWithCompletion('Восстановить данные?', $fileNameList, $fileNameList[0]);

                $this->restoreService->restore($value);
                $this->info('Данные восстановлены');
            }

            if ($validateAll || $validateDuplicatedRoles) {
                $this->validateUsersWithDuplicatedRoles($this->repository->findUsersWithDuplicatedRoles());
            }

            if ($validateAll || $validateRoles) {
                $this->validateRoleRelationsWithoutUsers($this->repository->findRoleRelationsWithoutUser());
            }

            if ($validateAll || $validateCompanies) {
                $this->validateCompaniesWithoutUser(
                    'Компании без пользователя',
                    $this->repository->findCompanies()
                );
                $this->validateEntitiesWithoutUserWithRole(
                    'Компании с пользователем, у которого нет роли client',
                    $this->repository->findCompanies(),
                    6
                );
                $this->validateEntitiesWithUsersHavingMultipleRoles(
                    'Компании с пользователями, у которых несколько ролей (включая роль client)',
                    $this->repository->findCompanies(),
                    6
                );
            }

            if ($validateAll || $validateDrivers) {
                $this->validateDriversWithoutUser('Водители без пользователя',
                    $this->repository->findDrivers());
                $this->validateEntitiesWithoutUserWithRole(
                    'Водители с пользователем, у которого нет роли driver',
                    $this->repository->findDrivers(),
                    3);
                $this->validateEntitiesWithUsersHavingMultipleRoles(
                    'Водители с пользователями, у которых несколько ролей (включая роль driver)',
                    $this->repository->findDrivers(),
                    3
                );
            }

            if ($validateAll || $validateUsers) {
                $this->validateUsersWithoutCompany(
                    'Пользователи с ролью client без компании',
                    $this->repository->findUsers()
                );
                $this->validateUsersWithoutDriver(
                    'Пользователи с ролью driver без водителя',
                    $this->repository->findUsers()
                );
            }

            if ($this->fixNeeded) {
                $this->logsGenerator->generate();
            }

            DB::commit();

            $this->info('Завершено');
        } catch (Exception $e) {
            DB::rollBack();

            $this->error("Ошибка: " . $e->getMessage());
        }
    }

    private function validateUsersWithDuplicatedRoles(array $entities)
    {
        $this->iterate(
            'Пользователи, с повторяющимися ролями',
            $entities,
            function () {
                return true;
            },
            function ($entity) {
                $user = User::find($entity->id);
                $roles = $user->roles()->get();

                $uniqueRoles = [];

                foreach ($roles as $role) {
                    if (!in_array($role->id, $uniqueRoles)) {
                        $uniqueRoles[] = $role->id;
                    }
                }

                $user->roles()->detach();
                $user->roles()->attach($uniqueRoles);
            },
            false
        );
    }

    private function validateRoleRelationsWithoutUsers(array $roles)
    {
        if ($this->fixNeeded) {
            $this->repository->deleteRoleRelationsWithoutUser($roles);
        }

        $this->iterate(
            'Роли у несуществующих пользователей',
            $roles,
            function () {
                return true;
            },
            function ($entity) {
                $this->logsGenerator->putValue(RestorationDataType::deletedRoleRelations(), [
                    'role_id' => $entity->role_id,
                    'model_id' => $entity->model_id,
                ]);
            },
            false
        );
    }

    private function validateCompaniesWithoutUser(string $title, array $companies)
    {
        $this->iterate(
            $title,
            $companies,
            function ($company) {
                return !count($company->role_ids);
            },
            function ($company) {
                $id = $this->createUser(
                    $company->hash_id,
                    $company->name,
                    $company->id,
                    6,
                    12,
                    '0'
                );

                $this->logsGenerator->putValue(RestorationDataType::createdUsers(), $id);
            });
    }

    private function validateDriversWithoutUser(string $title, array $drivers)
    {
        $this->iterate(
            $title,
            $drivers,
            function ($driver) {
                return !count($driver->role_ids);
            },
            function ($driver) {
                $id = $this->createUser(
                    $driver->hash_id,
                    $driver->fio,
                    $driver->company_id,
                    3,
                    3
                );

                $this->logsGenerator->putValue(RestorationDataType::createdUsers(), $id);
            });
    }

    private function validateEntitiesWithoutUserWithRole(string $title, array $entities, int $requiredRole)
    {
        $this->iterate(
            $title,
            $entities,
            function ($entity) use ($requiredRole) {
                $roleIds = $entity->role_ids;
                return !in_array($requiredRole, $roleIds) && count($roleIds);
            }
        );
    }

    private function validateEntitiesWithUsersHavingMultipleRoles(string $title, array $entities, int $requiredRole)
    {
        $this->iterate(
            $title,
            $entities,
            function ($entity) use ($requiredRole) {
                $roleIds = $entity->role_ids;
                return in_array($requiredRole, $roleIds) && count($roleIds) > 1;
            },
            function ($entity) use ($title, $requiredRole) {
                $user = User::find($entity->user_id);

                if (!$user) {
                    throw new Exception('validateEntitiesWithUsersHavingMultipleRoles: user not found');
                }

                $result = $user->roles()->sync([$requiredRole]);

                $this->logsGenerator->putByKey(
                    RestorationDataType::detachedRolesFromUser(),
                    $user->id,
                    $result['detached']
                );
            }
        );
    }

    private function validateUsersWithoutCompany(string $title, array $entities)
    {
        $this->iterate(
            $title,
            $entities,
            function ($entity) {
                $roleIds = $entity->role_ids;
                return in_array(6, $roleIds) && !$entity->company_id;
            },
            function ($entity) use ($title) {
                $user = User::find($entity->id);

                if (!$user) {
                    throw new Exception("validateUsersWithoutCompany: user {$entity->id} not found");
                }

                $user->deleted_at = new DateTimeImmutable();
                $user->save();

                $this->logsGenerator->putValue(RestorationDataType::deletedUsers(), $user->id);
            }
        );
    }

    private function validateUsersWithoutDriver(string $title, array $entities)
    {
        $this->iterate(
            $title,
            $entities,
            function ($entity) {
                $roleIds = $entity->role_ids;
                return in_array(3, $roleIds) && !$entity->driver_id;
            },
            function ($entity) use ($title) {
                $user = User::find($entity->id);

                if (!$user) {
                    throw new Exception("validateUsersWithoutDriver: user {$entity->id} not found");
                }

                $user->deleted_at = new DateTimeImmutable();
                $user->save();

                $this->logsGenerator->putValue(RestorationDataType::deletedUsers(), $user->id);
            }
        );
    }

    private function iterate(
        string  $title,
        array   $entities,
        Closure $condition,
        Closure $fixAction = null,
        bool    $logIds = true
    )
    {
        $count = 0;
        $fixedCount = 0;
        $this->comment($title . ':');

        foreach ($entities as $entity) {
            if ($condition($entity)) {
                if ($this->fixNeeded && $fixAction) {
                    $fixAction($entity);
                    $fixedCount++;
                } else {
                    if ($logIds) {
                        $separator = $count ? ', ' : '';
                        $this->output->write($separator . $entity->id);
                    }

                    $count++;
                }
            }
        }

        if ($count && $logIds) {
            $this->line('');
        }

        if ($count) {
            $this->error("Найдено: $count");
            $this->line('');
        } else if ($this->fixNeeded) {
            $this->info("Исправлено: $fixedCount\n");
        } else {
            $this->info("Ничего не найдено\n");
        }
    }

    /**
     * @throws Exception
     */
    private function createUser(
        string $hashId,
        string $name,
        string $companyId,
        int    $role,
        int    $roleUser,
        string $loginPrefix = '')
    {
        $validator = function (int $hashId) {
            if (User::where('hash_id', $hashId)->first()) {
                return false;
            }

            return true;
        };

        $userHashId = $this->generateHashId(
            $validator,
            config('app.hash_generator.user.min'),
            config('app.hash_generator.user.max'),
            config('app.hash_generator.user.tries')
        );

        $userLogin = $loginPrefix . $hashId;
        $email = $hashId . '-' . $userHashId . '@ta-7.ru';

        $user = User::create([
            'hash_id' => $userHashId,
            'email' => $email,
            'api_token' => Hash::make(date('H:i:s') . sha1($hashId)),
            'login' => $userLogin,
            'password' => Hash::make($userLogin),
            'name' => $name,
            'role' => $roleUser,
            'company_id' => $companyId
        ]);

        $user->roles()->attach($role);

        $this->info("Добавлен новый пользователь (login: $userLogin)");

        return $user->id;
    }
}
