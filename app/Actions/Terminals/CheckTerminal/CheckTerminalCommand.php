<?php

namespace App\Actions\Terminals\CheckTerminal;

use Carbon\Carbon;

final class CheckTerminalCommand
{
    /**
     * @var int
     */
    private $terminalId;
    /**
     * @var string
     */
    private $serialNumber;
    /**
     * @var Carbon
     */
    private $dateCheck;

    /**
     * @param int $terminalId
     * @param string $serialNumber
     * @param Carbon $dateCheck
     */
    public function __construct(int $terminalId, string $serialNumber, Carbon $dateCheck)
    {
        $this->terminalId = $terminalId;
        $this->serialNumber = $serialNumber;
        $this->dateCheck = $dateCheck;
    }

    public function getTerminalId(): int
    {
        return $this->terminalId;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getDateCheck(): Carbon
    {
        return $this->dateCheck;
    }
}