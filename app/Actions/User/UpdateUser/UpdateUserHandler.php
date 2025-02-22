<?php

namespace App\Actions\User\UpdateUser;

use Illuminate\Support\Facades\Hash;

final class UpdateUserHandler
{
    public function handle(UpdateUserCommand $command)
    {
        $user = $command->getUser();

        $user->login = $command->getLogin();
        $user->email = $command->getEmail();
        $user->timezone = $command->getTimezone();

        if ($command->getPassword()) {
            $user->password = Hash::make($command->getPassword());
        }

        $user->roles()->sync($command->getRoles());
        $user->permissions()->sync($command->getPermissions());

        $user->save();

        return $user;
    }
}