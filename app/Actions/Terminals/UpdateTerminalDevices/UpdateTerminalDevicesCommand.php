<?php

namespace App\Actions\Terminals\UpdateTerminalDevices;

final class UpdateTerminalDevicesCommand
{
    /**
     * @var int
     */
    private $terminalId;
    /**
     * @var TerminalDeviceItem[]
     */
    private $devices;

    /**
     * @param int $terminalId
     * @param TerminalDeviceItem[] $devices
     */
    public function __construct(int $terminalId, array $devices)
    {
        $this->terminalId = $terminalId;
        $this->devices = $devices;
    }

    public function getTerminalId(): int
    {
        return $this->terminalId;
    }

    public function getDevices(): array
    {
        return $this->devices;
    }
}