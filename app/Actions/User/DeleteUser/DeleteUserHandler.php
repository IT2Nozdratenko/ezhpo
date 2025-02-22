<?php

namespace App\Actions\User\DeleteUser;

final class DeleteUserHandler
{
    public function handle(DeleteUserCommand $command)
    {
        $command->getUser()->delete();
    }
}