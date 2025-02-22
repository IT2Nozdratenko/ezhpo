<?php

namespace App\Actions\Employees\UpdateEmployee;

use App\Actions\User\UpdateUser\UpdateUserCommand;
use App\Employee;
use App\User;
use Illuminate\Contracts\Bus\Dispatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateEmployeeHandler
{
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

    public function handle(UpdateEmployeeCommand $command)
    {
        $employee = Employee::withTrashed()->find($command->getId());
        if (!$employee) {
            throw new NotFoundHttpException('Сотрудник не найден');
        }

        $user = $employee->user()->withTrashed()->first();
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        $existedUser = User::withTrashed()
            ->where('id', '!=', $user->id)
            ->where('login', '=', $command->getLogin())
            ->first();

        if ($existedUser) {
            throw new \DomainException('Пользователь с таким логином уже существует');
        }

        $employee->update([
            'name' => $command->getName(),
            'blocked' => $command->getBlocked(),
            'pv_id' => $command->getPvId(),
            'eds' => $command->getEds(),
            'validity_eds_start' => $command->getValidityEdsStart(),
            'validity_eds_end' => $command->getValidityEdsEnd(),
        ]);

        $employee->points()->sync($command->getPvs());

        $this->dispatcher->dispatch(new UpdateUserCommand(
            $user,
            $command->getLogin(),
            $command->getEmail(),
            $command->getPassword(),
            $command->getTimezone(),
            $command->getRoles(),
            $command->getPermissions()
        ));
    }
}