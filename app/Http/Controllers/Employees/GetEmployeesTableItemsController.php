<?php

namespace App\Http\Controllers\Employees;

use App\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class GetEmployeesTableItemsController extends Controller
{
    public function __invoke(Request $request)
    {
        $builder = Employee::query()
            ->with([
                'pv',
                'user' => function ($query) {
                    return $query->withTrashed();
                },
                'user.roles',
            ]);

        if ($request->get('deleted')) {
            $builder->with(['whoDeleted'])->onlyTrashed();
        }

        $ids = $request->get('employee_id');
        if ($ids) {
            $builder->whereIn('id', $ids);
        }

        $email = $request->get('email');
        if ($email) {
            $builder->whereHas('user', function ($query) use ($email) {
                $query->where('email', 'like', "%$email%");
            });
        }

        $pvId = $request->get('point_id');
        if ($pvId) {
            $builder->where('pv_id', $pvId);
        }

        $role = $request->get('role');
        if ($role) {
            $builder->whereHas('user', function ($query) use ($role) {
                $query->whereHas('roles', function ($subQuery) use ($role) {
                    $subQuery->where('role_id', '=', $role);
                });
            });
        }

        $sortBy = $request->get('sortBy', 'id');
        $sortOrder = $request->get('sortDesc') == 'true' ? 'DESC' : 'ASC';

        if ($sortBy) {
            if ($sortBy == 'roles') {
                $builder
                    ->join('users', 'users.id', '=', 'employees.related_user_id')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', function ($join) use ($sortOrder) {
                        $join->on('model_has_roles.role_id', '=', 'roles.id')
                            ->orderBy('roles.guard_name', $sortOrder);
                    })
                    ->select('employees.*', 'guard_name')
                    ->orderBy('roles.guard_name', $sortOrder)
                    ->groupBy(['employees.id']);
            } else {
                $userAttrs = ['login', 'email', 'timezone', 'photo'];

                if (in_array($sortBy, $userAttrs)) {
                    $builder
                        ->join('users', 'users.id', '=', 'employees.related_user_id')
                        ->select('employees.*', 'users.' . $sortBy)
                        ->orderBy("users.$sortBy", $sortOrder)
                        ->groupBy(['employees.id']);
                } else {
                    $builder->orderBy("employees.$sortBy", $sortOrder);
                }
            }
        }

        $paginator = $builder->paginate($request->get('take', 1));

        $employees = $paginator
            ->getCollection()
            ->map(function (Employee $employee) {
                $roles = $employee->user->roles->map(function ($role) {
                    return $role->guard_name;
                });

                $whoDeleted = null;
                if ($employee->whoDeleted) {
                    $whoDeleted = $employee->whoDeleted->getEntityName();
                }

                $deletedAt = null;
                if ($employee->deleted_at) {
                    $deletedAt = $employee->deleted_at->format('Y-m-d H:i:s');
                }

                $pvName = null;
                if ($employee->pv) {
                    $pvName = $employee->pv->name;
                }

                return [
                    'id' => $employee->id,
                    'hash_id' => $employee->hash_id,
                    'photo' => $employee->user->photo,
                    'name' => $employee->name,
                    'login' => $employee->user->login,
                    'email' => $employee->user->email,
                    'pv' => $pvName,
                    'timezone' => $employee->user->timezone,
                    'blocked' => $employee->blocked,
                    'roles' => $roles,
                    'who_deleted' => $whoDeleted,
                    'deleted_at' => $deletedAt,
                    'user_id' => $employee->user->id,
                ];
            });

        return response([
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'items' => $employees,
        ]);
    }
}