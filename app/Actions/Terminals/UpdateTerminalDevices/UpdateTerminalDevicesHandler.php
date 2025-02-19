<?php

namespace App\Actions\Terminals\UpdateTerminalDevices;

use App\TerminalDevice;

final class UpdateTerminalDevicesHandler
{
    public function handle(UpdateTerminalDevicesCommand $command)
    {
        TerminalDevice::where('terminal_id', '=', $command->getTerminalId())->forceDelete();

        foreach ($command->getDevices() as $device) {
            $this->validateSerialNumberUnique($device->getSerialNumber());

            TerminalDevice::create([
                'terminal_id' => $command->getTerminalId(),
                'device_name' => $device->getSlug(),
                'device_serial_number' => $device->getSerialNumber(),
            ]);
        }}

    private function validateSerialNumberUnique(string $serialNumber)
    {
        $existedDevice = TerminalDevice::query()
            ->where('device_serial_number', '=', $serialNumber)
            ->whereNull('deleted_at')
            ->first();

        if ($existedDevice) {
            $relatedTerminal = $existedDevice->user;

            $errorMessage = 'Указанный серийный номер оборудования уже используется';

            if ($relatedTerminal) {
                $errorMessage .= '<br><br>Терминал: ' . $relatedTerminal->name;
            }

            throw new \DomainException($errorMessage);
        }
    }
}