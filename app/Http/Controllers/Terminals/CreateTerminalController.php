<?php

namespace App\Http\Controllers\Terminals;

use App\Actions\Terminals\CheckTerminal\CheckTerminalCommand;
use App\Actions\Terminals\CheckTerminal\CheckTerminalHandler;
use App\Actions\Terminals\CreateTerminal\CreateTerminalCommand;
use App\Actions\Terminals\CreateTerminal\CreateTerminalHandler;
use App\Actions\Terminals\UpdateTerminalDevices\TerminalDeviceItem;
use App\Actions\Terminals\UpdateTerminalDevices\UpdateTerminalDevicesCommand;
use App\Actions\Terminals\UpdateTerminalDevices\UpdateTerminalDevicesHandler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class CreateTerminalController
{
    public function __invoke(
        Request                      $request,
        CreateTerminalHandler        $createTerminalHandler,
        CheckTerminalHandler         $checkTerminalHandler,
        UpdateTerminalDevicesHandler $updateTerminalDevicesHandler
    )
    {
        DB::beginTransaction();

        try {
            $terminalId = $createTerminalHandler->handle(new CreateTerminalCommand(
                $request->input('name'),
                $request->input('timezone'),
                $request->input('company_id'),
                $request->input('blocked'),
                $request->input('pv_id'),
                $request->input('stamp_id')
            ));

            $checkTerminalHandler->handle(new CheckTerminalCommand(
                $terminalId,
                $request->input('serial_number'),
                Carbon::parse($request->input('date_check'))
            ));

            $updateTerminalDevicesHandler->handle(new UpdateTerminalDevicesCommand(
                $terminalId,
                array_map(function ($device) {
                    return new TerminalDeviceItem(
                        $device['slug'],
                        $device['serial_number'],
                    );
                }, $request->input('devices'))
            ));

            DB::commit();

            return response()->json()->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}