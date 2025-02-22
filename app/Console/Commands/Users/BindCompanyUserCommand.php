<?php

namespace App\Console\Commands\Users;

use App\Company;
use App\Enums\UserEntityType;
use App\Enums\UserRoleEnum;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class BindCompanyUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:bind-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавление компаниям связи с пользователем';

    public function handle()
    {
        User::withTrashed()
            ->with([
                'roles',
            ])
            ->whereNull('entity_type')
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', UserRoleEnum::CLIENT);
            })
            ->chunk(1000, function ($users) {
                $companyMap = Company::withTrashed()
                    ->whereIn(DB::raw("concat('0', hash_id)"), $users->pluck('login')->toArray())
                    ->get()
                    ->keyBy(function (Company $company) {
                        return '0' . $company->hash_id;
                    });

                $usersWithoutRelatedEntity = [];
                $companiesToUpdate = [];
                $usersToUpdate = [];
                foreach ($users as $user) {
                    $relatedCompany = $companyMap->get($user->login);

                    if (!$relatedCompany) {
                        $usersWithoutRelatedEntity[] = $user->id;
                        continue;
                    }

                    $usersToUpdate[] = $user->id;

                    $companiesToUpdate[] = [
                        'id' => $relatedCompany->id,
                        'related_user_id' => $user->id,
                    ];
                }

                if (count($usersWithoutRelatedEntity)) {
                    $this->info('Пользователи без компании ('.count($usersWithoutRelatedEntity).'):');
                    $this->warn(implode(', ', $usersWithoutRelatedEntity));
                }

                DB::beginTransaction();

                try {
                    DB::table('users')
                        ->whereIn('id', $usersToUpdate)
                        ->update(['entity_type' => UserEntityType::COMPANY]);

                    foreach ($companiesToUpdate as $item) {
                        DB::table('companies')
                            ->where('id','=', $item['id'])
                            ->update([
                                'related_user_id' => $item['related_user_id'],
                            ]);
                    }

                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();

                    throw $exception;
                }
            });
    }
}
