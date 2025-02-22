<?php

namespace App\Http\Controllers\Employees;

use App\Actions\Employees\RestoreEmployee\RestoreEmployeeCommand;
use App\Actions\Employees\RestoreEmployee\RestoreEmployeeHandler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RestoreEmployeeController extends Controller
{
    public function __invoke(int $id, RestoreEmployeeHandler $handler)
    {
        DB::beginTransaction();

        try {
            $handler->handle(new RestoreEmployeeCommand($id));

            DB::commit();

            return response()->json()->setStatusCode(Response::HTTP_CREATED);
        } catch (NotFoundHttpException $exception) {
            DB::rollBack();

            return response()
                ->json([
                    'message' => $exception->getMessage(),
                ])
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}