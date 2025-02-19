<?php

namespace App\Console\Commands\Users;

use App\Employee;
use App\Enums\UserEntityType;
use App\Enums\UserRoleEnum;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class EmployeesDataTransferCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:transfer-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание сущностей сотрудников, добавление связи с пользователем';

    public function handle()
    {
        $users = User::withTrashed()
            ->with([
                'roles',
            ])
            ->whereNull('entity_id')
            ->where(function ($query) {
                $query->whereDoesntHave('roles')
                    ->orWhereHas('roles', function ($q) {
                        $q->whereNotIn('roles.id', [UserRoleEnum::DRIVER, UserRoleEnum::CLIENT, UserRoleEnum::TERMINAL]);
                    });
            })
            ->get();

        DB::beginTransaction();

        try {
            $users->each(function (User $user) {
                if ($user->entity_type || $user->entity_id) {
                    return;
                }

                $employee = Employee::create([
                    'hash_id' => $user->hash_id,
                    'name' => $user->name,
                    'blocked' => $user->blocked,
                    'pv_id' => $user->pv_id,
                    'eds' => $user->eds,
                    'validity_eds_start' => $user->validity_eds_start,
                    'validity_eds_end' => $user->validity_eds_end,
                    'auto_created' => 1,
                    'deleted_at' => $user->deleted_at,
                    'deleted_id' => $user->deleted_id,
                ]);

                $user->entity_id = $employee->id;
                $user->entity_type = UserEntityType::EMPLOYEE;
                $user->save();
            });

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
