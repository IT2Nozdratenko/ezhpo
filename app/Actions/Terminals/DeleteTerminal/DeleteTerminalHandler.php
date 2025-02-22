<?php

namespace App\Actions\Terminals\DeleteTerminal;

use App\Actions\User\DeleteUser\DeleteUserCommand;
use App\Terminal;
use Illuminate\Contracts\Bus\Dispatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeleteTerminalHandler
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

    public function handle(DeleteTerminalCommand $command)
    {
        $terminal = Terminal::find($command->getId());

        if (!$terminal) {
            throw new NotFoundHttpException('Терминал не найден');
        }

        $user = $terminal->user;
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        $terminal->delete();

        $this->dispatcher->dispatch(new DeleteUserCommand($user));
    }
}