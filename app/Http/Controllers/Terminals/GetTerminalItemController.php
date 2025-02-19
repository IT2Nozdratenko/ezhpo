<?php

namespace App\Http\Controllers\Terminals;

use App\Enums\UserEntityType;
use App\Models\Forms\Form;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class GetTerminalItemController
{
    public function __invoke(int $id)
    {
        $terminal = DB::table('terminals as t')
            ->select([
                't.id',
                't.name',
                't.blocked',
                'c.id as company_id',
                'c.hash_id as company_hash_id',
                'c.name as company_name',
                's.id as stamp_id',
                's.name as stamp_name',
                'p.id as pv_id',
                'u.timezone',
                'tc.date_check',
                'tc.serial_number',
            ])
            ->leftJoin('points as p', 'p.id', '=', 't.pv_id')
            ->leftJoin('towns as pt', 'p.pv_id', '=', 'pt.id')
            ->leftJoin('stamps as s', 's.id', '=', 't.stamp_id')
            ->leftJoin('terminal_checks as tc', 'tc.terminal_id', '=', 't.id')
            ->leftJoin('users as u', function ($join) {
                $join->on('u.entity_id', '=', 't.id')
                    ->where('u.entity_type', '=', UserEntityType::terminal());
            })
            ->leftJoin('companies as c', 'u.company_id', '=', 'c.id')
            ->where('t.id', '=', $id)
            ->first();

        if (!$terminal) {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $terminalDevices = DB::table('terminal_devices as td')
            ->select([
                'device_name',
                'device_serial_number',
            ])
            ->where('td.terminal_id', '=', $id)
            ->get();

        $terminal->devices = $terminalDevices->toArray();

        return response()
            ->json($terminal)
            ->setStatusCode(Response::HTTP_OK);
    }
}