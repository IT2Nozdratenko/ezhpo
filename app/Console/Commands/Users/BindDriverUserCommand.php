<?php

namespace App\Console\Commands\Users;

use App\Driver;
use App\Enums\UserEntityType;
use App\Enums\UserRoleEnum;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class BindDriverUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:bind-drivers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавление водителям связи с пользователем';

    public function handle()
    {
        $users = User::withTrashed()
            ->with([
                'roles',
            ])
            ->whereNull('entity_type')
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', UserRoleEnum::DRIVER);
            })
            ->chunk(1000, function ($users) {
                $driverMap = Driver::withTrashed()
                    ->whereIn('hash_id', $users->pluck('login')->toArray())
                    ->get()
                    ->keyBy(function (Driver $driver) {
                        return $driver->hash_id;
                    });

                $usersWithoutRelatedEntity = [];
                $driversToUpdate = [];
                $usersToUpdate = [];
                foreach ($users as $user) {
                    $relatedDriver = $driverMap->get($user->login);

                    if (!$relatedDriver) {
                        $usersWithoutRelatedEntity[] = $user->id;
                        continue;
                    }

                    $usersToUpdate[] = $user->id;

                    $driversToUpdate[] = [
                        'id' => $relatedDriver->id,
                        'related_user_id' => $user->id,
                    ];
                }

                if (count($usersWithoutRelatedEntity)) {
                    $this->info('Пользователи без водителя ('.count($usersWithoutRelatedEntity).'):');
                    $this->warn(implode(', ', $usersWithoutRelatedEntity));
                }

                DB::beginTransaction();

                try {
                    DB::table('users')
                        ->whereIn('id', $usersToUpdate)
                        ->update(['entity_type' => UserEntityType::DRIVER]);

                    foreach ($driversToUpdate as $item) {
                        DB::table('drivers')
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
