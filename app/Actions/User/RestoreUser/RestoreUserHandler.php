<?php

namespace App\Actions\User\RestoreUser;

final class RestoreUserHandler
{
    public function handle(RestoreUserCommand $command)
    {
        $command->getUser()->restore();
    }
}