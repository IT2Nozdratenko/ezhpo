<?php

namespace App\Actions\Terminals\CreateTerminal;

use App\Actions\User\CreateUser\CreateUserCommand;
use App\Enums\UserEntityType;
use App\Enums\UserRoleEnum;
use App\GenerateHashIdTrait;
use App\Terminal;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Hash;

final class CreateTerminalHandler
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

    public function handle(CreateTerminalCommand $command)
    {
        $hashId = $this->resolveHashId();

        $terminal = Terminal::create([
            'hash_id' => $hashId,
            'name' => $command->getName(),
            'blocked' => $command->getBlocked(),
            'pv_id' => $command->getPvId(),
            'stamp_id' => $command->getStampId(),
        ]);

        $apiToken = Hash::make(date('H:i:s'));

        $this->dispatcher->dispatch(new CreateUserCommand(
            $terminal->id,
            UserEntityType::terminal(),
            time() . '@ta-7.ru',
            time() . '@ta-7.ru',
            $apiToken,
            $command->getTimezone(),
            $apiToken,
            $command->getCompanyId(),
            0,
            [UserRoleEnum::TERMINAL]
        ));

        return $terminal->id;
    }

    private function resolveHashId(): int
    {
        $validator = function (int $hashId) {
            if (Terminal::where('hash_id', $hashId)->first()) {
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