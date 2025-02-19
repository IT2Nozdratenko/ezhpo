<?php

namespace App\Http\Controllers\Terminals;

use App\Actions\Terminals\DeleteTerminal\DeleteTerminalCommand;
use App\Actions\Terminals\DeleteTerminal\DeleteTerminalHandler;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeleteTerminalController
{
    public function __invoke(int $id, DeleteTerminalHandler $handler)
    {
        DB::beginTransaction();

        try {
            $handler->handle(new DeleteTerminalCommand($id));

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
                    'message' => $exception->getMessage(),
                ])
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}