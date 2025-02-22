<?php

namespace App\Actions\Employees\CreateEmployee;

use App\Actions\User\CreateUser\CreateUserCommand;
use App\Employee;
use App\Enums\UserEntityType;
use App\GenerateHashIdTrait;
use App\User;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Hash;

final class CreateEmployeeHandler
{
    use GenerateHashIdTrait;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(CreateEmployeeCommand $command)
    {
        $existedUser = User::withTrashed()->where('login', '=', $command->getLogin())->first();

        if ($existedUser) {
            throw new \DomainException('Пользователь с таким логином уже существует');
        }

        $apiToken = Hash::make(date('H:i:s') . sha1($command->getPassword()));

        $user = $this->dispatcher->dispatch(new CreateUserCommand(
            UserEntityType::employee(),
            $command->getLogin(),
            $command->getEmail(),
            $command->getPassword(),
            $command->getTimezone(),
            $apiToken,
            null,
            0,
            $command->getRoles(),
            $command->getPermissions()
        ));

        $hashId = $this->resolveHashId();

        $employee = Employee::create([
            'related_user_id' => $user->id,
            'hash_id' => $hashId,
            'name' => $command->getName(),
            'blocked' => $command->getBlocked(),
            'pv_id' => $command->getPvId(),
            'eds' => $command->getEds(),
            'validity_eds_start' => $command->getValidityEdsStart(),
            'validity_eds_end' => $command->getValidityEdsEnd(),
        ]);

        $employee->points()->sync($command->getPvs());
    }

    private function resolveHashId(): int
    {
        $validator = function (int $hashId) {
            if (Employee::where('hash_id', $hashId)->first()) {
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