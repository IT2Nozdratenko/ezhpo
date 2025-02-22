<?php

namespace App\Console\Commands\Users;

use App\Enums\UserEntityType;
use App\Enums\UserRoleEnum;
use App\Terminal;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class TerminalsDataTransferCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:transfer-terminals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание сущностей терминалов, добавление связи с пользователем';

    public function handle()
    {
        $users = User::withTrashed()
            ->with([
                'roles',
            ])
            ->whereNull('entity_type')
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', UserRoleEnum::TERMINAL);
            })
            ->get();

        DB::beginTransaction();

        try {
            $users->each(function (User $user) {
                $terminal = Terminal::create([
                    'hash_id' => $user->hash_id,
                    'related_user_id' => $user->id,
                    'name' => $user->name,
                    'blocked' => $user->blocked,
                    'pv_id' => $user->pv_id,
                    'stamp_id' => $user->stamp_id,
                    'last_connection_at' > $user,
                    'auto_created' => 1,
                    'deleted_at' => $user->deleted_at,
                    'deleted_id' => $user->deleted_id,
                ]);

                DB::table('terminal_devices')
                    ->where('user_id', '=', $user->id)
                    ->update([
                        'terminal_id' => $terminal->id,
                    ]);

                DB::table('terminal_checks')
                    ->where('user_id', '=', $user->id)
                    ->update([
                        'terminal_id' => $terminal->id,
                    ]);

                $user->entity_type = UserEntityType::terminal();
                $user->save();
            });

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
