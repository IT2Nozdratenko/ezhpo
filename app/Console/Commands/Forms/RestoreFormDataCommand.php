<?php

namespace App\Console\Commands\Forms;

use App\Actions\Element\CreateElementHandlerFactory;
use App\Actions\User\CreateUserHandler;
use App\Anketa;
use App\Car;
use App\Company;
use App\Driver;
use App\Enums\FormFixStatusEnum;
use App\Point;
use App\Req;
use App\Town;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestoreFormDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forms:restore-foreign
                            {--reset-invalid : Убрать статус ошибок с низкой критичностью }
                            {--create-undefined : Восстановить несуществующие сущности }
                            {--count : Количество необработанных осмотров}
                            {--force : Запуск команды игнорируя конфиг}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Восстановление целостности данных осмотров';

    /** @var Company */
    private $defaultCompany;

    /** @var Town */
    private $defaultTown;

    /** @var Req */
    private $defaultReqs;

    /** @var Point */
    private $defaultPoint;

    /**
     * @var CreateElementHandlerFactory
     */
    private $factory;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->factory = new CreateElementHandlerFactory();

        set_time_limit(0);

        if ($this->option('count')) {
            $nonFixedForms = Anketa::query()
                ->where('fix_status', '>', FormFixStatusEnum::FIXED)
                ->count();

            $this->log("Всего необработанных осмотров - $nonFixedForms");

            return;
        }

        if ($this->option('reset-invalid')) {
            $this->resetInvalid();

            return;
        }

        if ($this->option('create-undefined')) {
            $this->createUndefined();

            return;
        }

        if ((config('forms.restore-foreign', false) === false) && !$this->option('force')) {
            return;
        }

        $chunkSize = config('forms.restore-foreign-chunk-size', 50000);

        try {
            $fixedForms = $this->fixForms($chunkSize);

            $this->log("Обработано осмотров - $fixedForms");
        } catch (Throwable $exception) {
            $this->log("Ошибка обработки группы осмотров - " . $exception->getMessage());
        }
    }

    private function log(string $message)
    {
        $this->info($message);
        Log::info($message);
    }

    private function resetInvalid()
    {
        $this->getDefaults();
        $this->resetTerminals();
        $this->resetUsers();
        $this->resetTerminalAndUser();
        $this->resetDrivers();
        $this->resetCars();
        $this->resetPoints();
    }

    private function resetTerminals()
    {
        $invalidStatus = FormFIxStatusConverter::fromStatuses([
            FormFixStatusEnum::INVALID_TERMINAL_ID
        ]);

        $fixed = Anketa::query()
            ->where('fix_status', $invalidStatus)
            ->update(['fix_status' => FormFixStatusEnum::FIXED]);

        $this->log("Сброшен статус у осмотров без терминала - $fixed");
    }

    private function resetUsers()
    {
        $invalidStatus = FormFIxStatusConverter::fromStatuses([
            FormFixStatusEnum::INVALID_USER_ID
        ]);

        $fixed = Anketa::query()
            ->where(function (Builder $query) {
                $query->whereNull('user_name')
                    ->orWhere('user_name', '');
            })
            ->where('fix_status', $invalidStatus)
            ->update(['fix_status' => FormFixStatusEnum::FIXED]);

        $this->log("Сброшен статус у осмотров без пользователя - $fixed");
    }

    private function resetTerminalAndUser()
    {
        $invalidStatuses = [];
        $invalidStatuses[] = FormFIxStatusConverter::fromStatuses([
            FormFixStatusEnum::INVALID_TERMINAL_ID,
            FormFixStatusEnum::INVALID_USER_ID
        ]);

        $fixed = Anketa::query()
            ->whereNull('user_name')
            ->whereIn('fix_status', $invalidStatuses)
            ->update(['fix_status' => FormFixStatusEnum::FIXED]);

        $this->log("Сброшен статус у осмотров без пользователя и терминала - $fixed");
    }

    private function resetDrivers()
    {
        $invalidStatus = FormFIxStatusConverter::fromStatuses([
            FormFixStatusEnum::INVALID_DRIVER_ID
        ]);

        $fixed = Anketa::query()
            ->where(function (Builder $query) {
                $query->whereNull('driver_fio')
                    ->orWhere('driver_fio', '');
            })
            ->where('fix_status', $invalidStatus)
            ->update(['fix_status' => FormFixStatusEnum::FIXED]);

        $this->log("Сброшен статус у осмотров без водителя - $fixed");
    }

    private function resetCars()
    {
        $invalidStatus = FormFIxStatusConverter::fromStatuses([
            FormFixStatusEnum::INVALID_CAR_ID
        ]);

        $fixed = Anketa::query()
            ->where(function (Builder $query) {
                $query->whereNull('car_gos_number')
                    ->orWhere('car_gos_number', '');
            })
            ->where('fix_status', $invalidStatus)
            ->update(['fix_status' => FormFixStatusEnum::FIXED]);

        $this->log("Сброшен статус у осмотров без авто - $fixed");
    }

    private function resetPoints()
    {
        $invalidStatus = FormFIxStatusConverter::fromStatuses([
            FormFixStatusEnum::INVALID_POINT_ID
        ]);

        $fixed = Anketa::query()
            ->where(function (Builder $query) {
                $query->whereNull('pv_id')
                    ->orWhere('pv_id', '0')
                    ->orWhere('pv_id', '');
            })
            ->where('fix_status', $invalidStatus)
            ->update(['fix_status' => FormFixStatusEnum::FIXED]);

        $this->log("Сброшен статус у осмотров без ПВ - $fixed");
    }

    private function getDefaults()
    {
        $this->getDefaultTown();
        $this->getDefaultReqs();
        $this->getDefaultCompany();
        $this->getDefaultPoint();
    }

    private function getDefaultTown()
    {
        $default = Town::withTrashed()
            ->where('auto_created', true)
            ->first();

        if (!$default) {
            $handler = $this->factory->make('Town');

            $default = $handler->handle([
                'name' => 'Нет данных',
                'deleted_at' => Carbon::now(),
                'auto_created' => true
            ]);
        }

        $this->defaultTown = $default;
    }

    private function getDefaultReqs()
    {
        $default = Req::withTrashed()
            ->where('auto_created', true)
            ->first();

        if (!$default) {
            $handler = $this->factory->make('Req');

            $default = $handler->handle([
                'name' => 'Нет данных',
                'deleted_at' => Carbon::now(),
                'auto_created' => true
            ]);
        }

        $this->defaultReqs = $default;
    }

    private function getDefaultCompany()
    {
        $default = Company::withTrashed()
            ->where('auto_created', true)
            ->first();

        if (!$default) {
            $handler = $this->factory->make('Company');

            $default = $handler->handle([
                'name' => 'Нет данных',
                'req_id' => $this->defaultReqs->id,
                'deleted_at' => Carbon::now(),
                'auto_created' => true
            ]);
        }

        $this->defaultCompany = $default;
    }

    private function getDefaultPoint()
    {
        $default = Point::withTrashed()
            ->where('auto_created', true)
            ->first();

        if (!$default) {
            $handler = $this->factory->make('Point');

            $default = $handler->handle([
                'name' => 'Нет данных',
                'pv_id' => $this->defaultTown->id,
                'deleted_at' => Carbon::now(),
                'auto_created' => true
            ]);
        }

        $this->defaultPoint = $default;
    }

    private function createUndefined()
    {
        $this->getDefaults();
        $this->createCompanies();
        $this->createPoints();
        $this->createUsers();
    }

    private function createCompanies()
    {
        $companyNames = Anketa::query()
            ->select(['company_name'])
            ->where('fix_status', '>', FormFixStatusEnum::FIXED)
            ->where(function (Builder $query) {
                $query->whereNotNull('company_name')
                    ->where('company_name', '!=', '');
            })
            ->whereNull('company_id')
            ->whereNull('car_id')
            ->whereNull('driver_id')
            ->groupBy(['company_name'])
            ->get()
            ->pluck('company_name')
            ->toArray();

        foreach ($companyNames as $companyName) {
            try {
                $created = $this->createCompany($companyName);

                $restored = Anketa::query()
                    ->where('fix_status', '>', FormFixStatusEnum::FIXED)
                    ->where('company_name', $companyName)
                    ->whereNull('company_id')
                    ->whereNull('car_id')
                    ->whereNull('driver_id')
                    ->update([
                        'company_id' => $created->hash_id
                    ]);

                $this->log("Восстановлено $restored записей у компании $companyName");
            } catch (Throwable $exception) {
                $this->log("Ошибка восстановления записей у компании $companyName - {$exception->getMessage()}");
            }
        }
    }

    private function createCompany(string $name): Company
    {
        $handler = $this->factory->make('Company');

        $created = Company::withTrashed()->where('name', $name)->first();

        if ($created) {
            return $created;
        }

        return $handler->handle([
            'name' => $name,
            'deleted_at' => Carbon::now(),
            'auto_created' => true
        ]);
    }

    private function createPoints()
    {
        $pointNames = Anketa::query()
            ->select(['pv_id'])
            ->where('fix_status', '>', FormFixStatusEnum::FIXED)
            ->where(function (Builder $query) {
                $query->whereNotNull('pv_id')
                    ->where('pv_id', '!=', '0')
                    ->where('pv_id', '!=', '');
            })
            ->where(function (Builder $query) {
                $query->whereNull('point_id')
                    ->orWhere('point_id', 0);
            })
            ->groupBy(['pv_id'])
            ->get()
            ->pluck('pv_id')
            ->toArray();

        foreach ($pointNames as $pointName) {
            try {
                $point = $this->createPoint($pointName);

                $restored = Anketa::query()
                    ->where('fix_status', '>', FormFixStatusEnum::FIXED)
                    ->where('pv_id', $pointName)
                    ->where(function (Builder $query) {
                        $query->whereNull('point_id')
                            ->orWhere('point_id', 0);
                    })
                    ->update([
                        'point_id' => $point->id
                    ]);

                $this->log("Восстановлено $restored записей у ПВ $pointName");
            } catch (Throwable $exception) {
                $this->log("Ошибка восстановления записей у ПВ $pointName - {$exception->getMessage()}");
            }
        }
    }

    private function createPoint(string $name): Point
    {
        $handler = $this->factory->make('Point');

        $created = Point::withTrashed()->where('name', $name)->first();

        if ($created) {
            return $created;
        }

        return $handler->handle([
            'name' => $name,
            'pv_id' => $this->defaultTown->id,
            'deleted_at' => Carbon::now(),
            'auto_created' => true
        ]);
    }

    private function createUsers()
    {
        $userNames = Anketa::query()
            ->select(['user_name'])
            ->where('fix_status', '>', FormFixStatusEnum::FIXED)
            ->where('user_id', 0)
            ->where(function (Builder $query) {
                $query->whereNotNull('user_name')
                    ->where('user_name', '!=', '');
            })
            ->groupBy(['user_name'])
            ->get()
            ->pluck('user_name')
            ->toArray();

        foreach ($userNames as $userName) {
            try {
                $created = $this->createUser($userName);

                $restored = Anketa::query()
                    ->where('fix_status', '>', FormFixStatusEnum::FIXED)
                    ->where('user_id', 0)
                    ->where('user_name', $userName)
                    ->update([
                        'user_id' => $created->id
                    ]);

                $this->log("Восстановлено $restored записей у пользователя $userName");
            } catch (Throwable $exception) {
                $this->log("Ошибка восстановления записей у пользователя $userName - {$exception->getMessage()}");
            }
        }
    }

    /**
     * @throws Exception
     */
    private function createUser(string $name): User
    {
        $login = str_replace(' ', '', $this->transliterate($name));

        if (strlen($login) === 0) {
            throw new Exception("Невалидное имя пользователя пользователя $name");
        }

        $email = "$login@ta-7.ru";

        $created = User::withTrashed()
            ->where('name', $name)
            ->orWhere('login', $email)
            ->first();

        if ($created) {
            return $created;
        }

        $handler = new CreateUserHandler();

        return $handler->handle([
            'name' => $name,
            'password' => $this->generateRandomString(),
            'email' => $email,
            'timezone' => 3,
            'pv' => $this->defaultPoint->id,
            'eds' => null,
            'validity_eds_start' => null,
            'validity_eds_end' => null,
            'deleted_at' => Carbon::now(),
            'auto_created' => true
        ]);
    }

    private function fixForms(int $chunkSize): int
    {
        $this->log("Восстановление $chunkSize записей");

        $this->getDefaults();

        $forms = Anketa::query()
            ->select([
                'id',
                'fix_status',
                'user_id',
                'user_name',
                'driver_id',
                'driver_fio',
                'car_id',
                'car_gos_number',
                'company_id',
                'company_name',
                'point_id',
                'pv_id'
            ])
            ->where('fix_status', '>', FormFixStatusEnum::FIXED)
            ->orderBy('id', 'desc')
            ->limit($chunkSize)
            ->get();

        $this->log("Получено $chunkSize записей");

        $all = 0;
        $restored = 0;

        foreach ($forms as $form) {
            if ($this->fixForm($form)) {
                $restored++;
            }

            $all++;

            if (($all % 1000) === 0) {
                $this->log("Обработано: $all, восстановлено: $restored");
            }
        }

        return $forms->count();
    }

    private function fixForm(Anketa $form): bool
    {
        $statuses = FormFIxStatusConverter::toStatuses($form->fix_status);

        $statuses = $this->resetStatus($statuses, FormFixStatusEnum::INVALID_TERMINAL_ID);

        if (in_array(FormFixStatusEnum::INVALID_COMPANY_ID, $statuses)) {
            $form = $this->fixCompany($form);

            if ($form->company_id) {
                $statuses = $this->resetStatus($statuses, FormFixStatusEnum::INVALID_COMPANY_ID);
            }
        }

        if (in_array(FormFixStatusEnum::INVALID_DRIVER_ID, $statuses)) {
            $form = $this->fixDriver($form);

            if ($form->driver_id) {
                $statuses = $this->resetStatus($statuses, FormFixStatusEnum::INVALID_DRIVER_ID);
            }
        }

        if (in_array(FormFixStatusEnum::INVALID_CAR_ID, $statuses)) {
            $form = $this->fixCar($form);

            if ($form->car_id) {
                $statuses = $this->resetStatus($statuses, FormFixStatusEnum::INVALID_CAR_ID);
            }
        }

        if (in_array(FormFixStatusEnum::INVALID_USER_ID, $statuses)) {
            $form = $this->fixUser($form);

            if ($form->user_id) {
                $statuses = $this->resetStatus($statuses, FormFixStatusEnum::INVALID_USER_ID);
            }
        }

        if (in_array(FormFixStatusEnum::INVALID_POINT_ID, $statuses)) {
            $form = $this->fixPoint($form);

            if ($form->point_id) {
                $statuses = $this->resetStatus($statuses, FormFixStatusEnum::INVALID_POINT_ID);
            }
        }

        $form->fix_status = FormFIxStatusConverter::fromStatuses($statuses);

        $form->save();

        return $form->fix_status === FormFixStatusEnum::FIXED;
    }

    private function resetStatus(array $statuses, string $filteredStatus): array
    {
        return array_filter($statuses, function ($status) use ($filteredStatus) {
            return $status != $filteredStatus;
        });
    }

    private function fixCompany(Anketa $form): Anketa
    {
        try {
            if (!$form->company_id && $form->driver_id) {
                $driver = Driver::withTrashed()->where('hash_id', $form->driver_id)->first();
                if ($driver && $driver->company_id) {
                    $company = Company::withTrashed()->find($driver->company_id);
                    if ($company) {
                        $form->company_id = $company->hash_id;
                    }
                }
            }

            if (!$form->company_id && $form->car_id) {
                $car = Car::withTrashed()->where('hash_id', $form->car_id)->first();
                if ($car && $car->company_id) {
                    $company = Company::withTrashed()->find($car->company_id);
                    if ($company) {
                        $form->company_id = $company->hash_id;
                    }
                }
            }

            if (!$form->company_id && $form->company_name) {
                $company = $this->createCompany($form->company_name);

                $form->company_id = $company->hash_id;
            }
        } catch (Throwable $exception) {
            $this->log("Ошибка восстановления записи у компании $form->company_name / $form->driver_id / $form->car_id  - {$exception->getMessage()}");
        }

        return $form;
    }

    private function fixDriver(Anketa $form): Anketa
    {
        if ($form->driver_id) {
            return $form;
        }

        if (!$form->driver_fio) {
            return $form;
        }

        try {
            $companyId = $this->defaultCompany->id;

            if ($form->company_id) {
                $company = Company::withTrashed()->where('hash_id', $form->company_id)->first();
                if ($company) {
                    $companyId = $company->id;
                }
            }

            $created = $this->createDriver(trim($form->driver_fio), $companyId);

            $form->driver_id = $created->hash_id;
        } catch (Throwable $exception) {
            $this->log("Ошибка восстановления записи у водителя $form->driver_fio - {$exception->getMessage()}");
        }

        return $form;
    }

    private function createDriver(string $name, $companyId): Driver
    {
        $handler = $this->factory->make('Driver');

        $created = Driver::withTrashed()
            ->where('fio', $name)
            ->where('company_id', $companyId)
            ->first();

        if ($created) {
            return $created;
        }

        return $handler->handle([
            'fio' => $name,
            'company_id' => $companyId,
            'deleted_at' => Carbon::now(),
            'auto_created' => true
        ]);
    }

    private function fixCar(Anketa $form): Anketa
    {
        if ($form->car_id) {
            return $form;
        }

        if (!$form->car_gos_number) {
            return $form;
        }

        try {
            $companyId = $this->defaultCompany->id;

            if ($form->company_id) {
                $company = Company::withTrashed()->where('hash_id', $form->company_id)->first();
                if ($company) {
                    $companyId = $company->id;
                }
            }

            $created = $this->createCar($form->car_gos_number, $companyId);

            $form->car_id = $created->hash_id;
        } catch (Throwable $exception) {
            $this->log("Ошибка восстановления записи у авто $form->user_name - {$exception->getMessage()}");
        }

        return $form;
    }

    private function createCar(string $gosNumber, $companyId): Car
    {
        $handler = $this->factory->make('Car');

        $created = Car::withTrashed()
            ->where('gos_number', $gosNumber)
            ->where('company_id', $companyId)
            ->first();

        if ($created) {
            return $created;
        }

        return $handler->handle([
            'gos_number' => $gosNumber,
            'company_id' => $companyId,
            'deleted_at' => Carbon::now(),
            'auto_created' => true
        ]);
    }

    private function fixUser(Anketa $form): Anketa
    {
        if ($form->user_id) {
            return $form;
        }

        if (!$form->user_name) {
            return $form;
        }

        try {
            $user = $this->createUser($form->user_name);

            $form->user_id = $user->id;
        } catch (Throwable $exception) {
            $this->log("Ошибка восстановления записи у пользователя $form->user_name - {$exception->getMessage()}");
        }

        return $form;
    }

    private function fixPoint(Anketa $form): Anketa
    {
        if ($form->point_id) {
            return $form;
        }

        if (!$form->pv_id) {
            return $form;
        }

        try {
            $point = $this->createPoint($form->pv_id);

            $form->point_id = $point->id;
        } catch (Throwable $exception) {
            $this->log("Ошибка восстановления записи у ПВ $form->pv_id - {$exception->getMessage()}");
        }

        return $form;
    }

    private function transliterate(string $text): string
    {
        $cyr = array(
            'ж', 'ч', 'щ', 'ш', 'ю', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
            'Ж', 'Ч', 'Щ', 'Ш', 'Ю', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я');
        $lat = array(
            'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
            'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q');

        return str_replace($cyr, $lat, $text);
    }

    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

}
