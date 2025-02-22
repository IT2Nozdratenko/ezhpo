<?php

namespace App\Http\Controllers\Employees;

use App\Actions\Employees\CreateEmployee\CreateEmployeeHandler;
use App\Actions\Employees\CreateEmployee\CreateEmployeeCommand;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\CreateEmployeeRequest;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class CreateEmployeeController extends Controller
{
    public function __invoke(CreateEmployeeRequest $request, CreateEmployeeHandler $handler)
    {
        DB::beginTransaction();

        try {
            $handler->handle(new CreateEmployeeCommand(
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

            return response()->json()->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}