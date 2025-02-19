<?php

namespace App\Actions\Terminals\CheckTerminal;

use App\TerminalCheck;
use Carbon\Carbon;

final class CheckTerminalHandler
{
    /**
     * @param CheckTerminalCommand $command
     * @return void
     */
    public function handle(CheckTerminalCommand $command)
    {
        $terminalCheck = TerminalCheck::query()
            ->where('terminal_id', '=', $command->getTerminalId())
            ->first();

        $this->validateSerialNumberUnique($command->getSerialNumber(), $command->getTerminalId());
        $this->validateDateCheck($command->getDateCheck());

        if ($terminalCheck) {
            $terminalCheck->update([
                'serial_number' => $command->getSerialNumber(),
                'date_check' => $command->getDateCheck(),
                'date_end_check' => $command->getDateCheck()->copy()->addYear()
            ]);
        }
        else {
            $terminalCheck = TerminalCheck::create([
                'terminal_id' => $command->getTerminalId(),
                'serial_number' => $command->getSerialNumber(),
                'date_check' => $command->getDateCheck(),
                'date_end_check' => $command->getDateCheck()->copy()->addYear()
            ]);
        }

        return $terminalCheck->id;
    }

    private function validateSerialNumberUnique(string $serialNumber, int $terminalId)
    {
        $terminals = TerminalCheck::query()
            ->where('serial_number', '=', $serialNumber)
            ->where('terminal_id', '!=', $terminalId)
            ->whereNull('deleted_at')
            ->get();

        if ($terminals->count()) {
            $terminal = $terminals[0]->user;
            $message = 'Указанный серийный номер терминала уже используется';

            if ($terminal) {
                $message .= '<br><br>Терминал: ' . $terminal->name;
            }

            throw new \DomainException($message);
        }
    }

    private function validateDateCheck(Carbon $dateCheck)
    {
        if (!$dateCheck->lessThanOrEqualTo(Carbon::now())) {
            throw new \DomainException('Дата поверки должна быть датой не позже сегодняшней даты или равной ей');
        }
    }
}