<?php

namespace App\Http\Controllers\Employees;

use App\Employee;
use App\Http\Controllers\Controller;
use App\Role;
use Symfony\Component\HttpFoundation\Response;

final class GetEmployeeItemController extends Controller
{
    public function __invoke(int $id)
    {
        $employee = Employee::withTrashed()
            ->with([
                'pv',
                'points',
                'user' => function($query) {
                    return $query->withTrashed();
                },
                'user.roles',
                'user.permissions',
            ])
            ->find($id);

        if (!$employee) {
            return response()->json([
                'message' => 'Сотрудник не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        $permissionIds = $employee->user->permissions
            ->pluck('id')
            ->values()
            ->toArray();

        $pvIds = $employee->points
            ->pluck('id')
            ->values()
            ->toArray();

        $pvId = null;
        if ($employee->pv) {
            $pvId = $employee->pv->id;
        }

        $roles = $employee->user->roles->map(function (Role $role) {
            return [
                'id' => $role->id,
                'guard_name' => $role->guard_name,
            ];
        });

        $item = [
            'id' => $employee->id,
            'name' => $employee->name,
            'login' => $employee->user->login,
            'email' => $employee->user->email,
            'eds' => $employee->eds,
            'timezone' => $employee->user->timezone,
            'pv_id' => $pvId,
            'pv_ids' => $pvIds,
            'roles' => $roles,
            'blocked' => $employee->blocked,
            'validity_eds_start' => $employee->validity_eds_start,
            'validity_eds_end' => $employee->validity_eds_end,
            'permission_ids' => $permissionIds,
        ];

        return response()->json($item)->setStatusCode(Response::HTTP_OK);
    }
}