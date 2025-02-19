<?php

namespace App\Http\Controllers\Terminals;

use App\Actions\Terminals\CheckTerminal\CheckTerminalCommand;
use App\Actions\Terminals\CheckTerminal\CheckTerminalHandler;
use App\Actions\Terminals\UpdateTerminal\UpdateTerminalCommand;
use App\Actions\Terminals\UpdateTerminal\UpdateTerminalHandler;
use App\Actions\Terminals\UpdateTerminalDevices\TerminalDeviceItem;
use App\Actions\Terminals\UpdateTerminalDevices\UpdateTerminalDevicesCommand;
use App\Actions\Terminals\UpdateTerminalDevices\UpdateTerminalDevicesHandler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateTerminalController
{
    public function __invoke(
        int                          $id,
        Request                      $request,
        UpdateTerminalHandler        $updateTerminalHandler,
        CheckTerminalHandler         $checkTerminalHandler,
        UpdateTerminalDevicesHandler $updateTerminalDevicesHandler
    )
    {
        DB::beginTransaction();

        try {
            $updateTerminalHandler->handle(new UpdateTerminalCommand(
                $id,
                $request->input('name'),
                $request->input('timezone'),
                $request->input('company_id'),
                $request->input('blocked'),
                $request->input('pv_id'),
                $request->input('stamp_id')
            ));

            $checkTerminalHandler->handle(new CheckTerminalCommand(
                $id,
                $request->input('serial_number'),
                Carbon::parse($request->input('date_check'))
            ));

            $updateTerminalDevicesHandler->handle(new UpdateTerminalDevicesCommand(
                $id,
                array_map(function ($device) {
                    return new TerminalDeviceItem(
                        $device['slug'],
                        $device['serial_number'],
                    );
                }, $request->input('devices'))
            ));

            DB::commit();

            return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
        } catch (NotFoundHttpException $exception) {
            DB::rollBack();

            return response()
                ->json([
                    'message' => $exception->getMessage(),
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()
                ->json([
                    'errors' => $exception->getMessage()
                ])
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}