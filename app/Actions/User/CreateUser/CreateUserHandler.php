<?php

namespace App\Actions\User\CreateUser;

use App\GenerateHashIdTrait;
use App\User;
use Illuminate\Support\Facades\Hash;

final class CreateUserHandler
{
    use GenerateHashIdTrait;

    public function handle(CreateUserCommand $command): User
    {
        $hashId = $this->resolveHashId();

        $user = User::create([
            'entity_type' => $command->getEntityType(),
            'hash_id' => $hashId, // todo: hash_id нужно будет убрать у users
            'email' => $command->getEmail(),
            'login' => $command->getLogin(),
            'password' => Hash::make($command->getPassword()),
            'api_token' => $command->getApiToken(),
            'timezone' => $command->getTimezone(),
            'company_id' => $command->getCompanyId(),
            'auto_created' => $command->getAutoCreated(),
        ]);

        $user->roles()->sync($command->getRoles());
        $user->permissions()->sync($command->getPermissions());

        return $user;
    }

    private function resolveHashId(): int
    {
        $validator = function (int $hashId) {
            if (User::where('hash_id', $hashId)->first()) {
                return false;
            }

            return true;
        };

        return $this->generateHashId(
            $validator,
            config('app.hash_generator.user.min'),
            config('app.hash_generator.user.max'),
            config('app.hash_generator.user.tries')
        );
    }
}