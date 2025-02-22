<?php

namespace App\Actions\Employees\RestoreEmployee;

use App\Actions\User\RestoreUser\RestoreUserCommand;
use App\Employee;
use Illuminate\Contracts\Bus\Dispatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RestoreEmployeeHandler
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

    public function handle(RestoreEmployeeCommand $command)
    {
        $employee = Employee::withTrashed()->find($command->getId());
        if (!$employee) {
            throw new NotFoundHttpException('Сотрудник не найден');
        }

        $user = $employee->user()->withTrashed()->first();
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        $employee->restore();

        $this->dispatcher->dispatch(new RestoreUserCommand($user));
    }
}