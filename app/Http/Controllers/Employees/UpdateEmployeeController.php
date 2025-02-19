<?php

namespace App\Http\Controllers\Employees;

use App\Actions\Employees\UpdateEmployee\UpdateEmployeeCommand;
use App\Actions\Employees\UpdateEmployee\UpdateEmployeeHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateEmployeeController extends Controller
{
    public function __invoke(int $id, UpdateEmployeeRequest $request, UpdateEmployeeHandler $handler)
    {
        DB::beginTransaction();

        try {
            $handler->handle(new UpdateEmployeeCommand(
                $id,
                $request->input('name'),
                $request->input('login'),
                $request->input('email'),
                $request->input('eds'),
                $request->input('timezone'),
                $request->input('password'),
                $request->input('pv_id'),
                $request->input('pvs'),
                $request->input('blocked'),
                $request->input('validity_eds_start'),
                $request->input('validity_eds_end'),
                $request->input('roles'),
                $request->input('permissions')
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

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}