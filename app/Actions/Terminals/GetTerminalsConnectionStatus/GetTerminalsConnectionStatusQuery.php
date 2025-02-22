<?php

namespace App\Actions\Terminals\GetTerminalsConnectionStatus;

use App\Terminal;
use Carbon\Carbon;

final class GetTerminalsConnectionStatusQuery
{
    public function get(GetTerminalsConnectionStatusParams $params)
    {
        $terminals = Terminal::query()
            ->whereIn('id', $params->getIds())
            ->select([
                'id',
                'last_connection_at'
            ])
            ->get();

        foreach ($terminals as $terminal) {
            $terminal->connected = false;
            if ($terminal->last_connection_at) {
                $terminal->connected = Carbon::now()->diffInSeconds($terminal->last_connection_at) < 20;
            }
        }

        return $terminals;
    }
}