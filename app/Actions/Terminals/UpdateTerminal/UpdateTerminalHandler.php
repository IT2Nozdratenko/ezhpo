<?php

namespace App\Actions\Terminals\UpdateTerminal;

use App\Actions\User\UpdateUser\UpdateUserCommand;
use App\Terminal;
use Illuminate\Contracts\Bus\Dispatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateTerminalHandler
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

    public function handle(UpdateTerminalCommand $command)
    {
        $terminal = Terminal::withTrashed()->find($command->getId());

        if (!$terminal) {
            throw new NotFoundHttpException('Терминал не найден');
        }

        $user = $terminal->user()->withTrashed()->first();
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        $terminal->name = $command->getName();
        $terminal->blocked = $command->getBlocked();
        $terminal->pv_id = $command->getPvId();
        $terminal->stamp_id = $command->getStampId();

        $terminal->save();

        $this->dispatcher->dispatch(new UpdateUserCommand(
            $user,
            $user->login,
            $user->email,
            $user->password,
            $command->getTimezone(),
            [],
            [],
        ));

        return $terminal->id;
    }
}