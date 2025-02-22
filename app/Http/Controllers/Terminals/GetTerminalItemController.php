<?php

namespace App\Http\Controllers\Terminals;

use App\Terminal;
use App\TerminalDevice;
use Symfony\Component\HttpFoundation\Response;

final class GetTerminalItemController
{
    public function __invoke(int $id)
    {
        $terminal = Terminal::withTrashed()
            ->with([
                'pv',
                'pv.town',
                'stamp',
                'terminalCheck',
                'terminalDevices',
                'user' => function ($query) {
                    return $query->withTrashed();
                },
                'user.company',
            ])
            ->where('id', '=', $id)
            ->first();

        if (!$terminal) {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $terminalDevices = $terminal->terminalDevices
            ->map(function (TerminalDevice $terminalDevice) {
                return [
                    'device_name' => $terminalDevice->device_name,
                    'device_serial_number' => $terminalDevice->device_serial_number,
                ];
            })
            ->toArray();

        $terminal = [
            'id' => $terminal->id,
            'name' => $terminal->name,
            'blocked' => $terminal->blocked,
            'company_id' => $terminal->user->company ? $terminal->user->company->id : null,
            'company_hash_id' => $terminal->user->company ? $terminal->user->company->hash_id : null,
            'company_name' => $terminal->user->company ? $terminal->user->company->name : null,
            'stamp_id' => $terminal->stamp ? $terminal->stamp->id : null,
            'stamp_name' => $terminal->stamp ? $terminal->stamp->name : '',
            'pv_id' => $terminal->pv_id,
            'timezone' => $terminal->user->timezone,
            'date_check' => $terminal->terminalCheck ? $terminal->terminalCheck->date_check->format('Y-m-d') : null,
            'serial_number' => $terminal->terminalCheck ? $terminal->terminalCheck->serial_number : null,
            'devices' => $terminalDevices,
        ];

        return response()
            ->json($terminal)
            ->setStatusCode(Response::HTTP_OK);
    }
}