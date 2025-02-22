<?php

namespace App\Http\Controllers\Employees;

use App\Employee;
use App\Enums\UserRoleEnum;
use App\FieldPrompt;
use App\Http\Controllers\Controller;
use App\Town;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class IndexEmployeesPageController extends Controller
{
    public function __invoke()
    {
        $fields = FieldPrompt::where('type', '=', 'employees')->get();

        $allPermissions = Permission::orderBy('guard_name')->get();

        $currentUserPermissions = [
            'permission_to_edit' => user()->access('employee_update'),
            'permission_to_view' => user()->access('employee_read'),
            'permission_to_create' => user()->access('employee_create'),
            'permission_to_delete' => user()->access('employee_delete'),
            'permission_to_trash' => user()->access('employee_trash'),
            'permission_to_logs_read' => user()->access('employee_logs_read')
        ];

        $roles = Role::query()->get();

        $rolesFilterOptions = $roles
            ->where('name', '!=', 'driver')
            ->map(function ($model) {
                return [
                    'id' => $model->id,
                    'text' => sprintf('[%s] %s', $model->id, $model->guard_name)
                ];
            })
            ->values()
            ->toArray();

        $rolesModalOptions = $roles
            ->whereNotIn('id', [
                UserRoleEnum::DRIVER,
                UserRoleEnum::CLIENT,
                UserRoleEnum::TERMINAL,
            ])
            ->map(function ($model) {
                return [
                    'id' => $model->id,
                    'text' => $model->guard_name
                ];
            })
            ->values()
            ->toArray();

        $clientRoleId = $roles
            ->where('id', UserRoleEnum::CLIENT)
            ->first()
            ->id;

        $headOperatorSdpoRoleId = $roles
            ->where('id', UserRoleEnum::HEAD_OPERATOR_SDPO)
            ->first()
            ->id;

        $points = Town::query()
            ->with(['pvs'])
            ->orderBy('towns.name')
            ->get();

        $pointsModalOptions = $points->map(function ($model) {
            $option['label'] = $model->name;

            if (!count($model->pvs)) {
                $option['options'] = [];
            }

            foreach ($model->pvs as $pv) {
                $option['options'][] = [
                    'value' => $pv['id'],
                    'text' => $pv['name']
                ];
            }

            return $option;
        });

        $pointsFilterOptions = $points
            ->reduce(function ($models, $model) {
                foreach ($model->pvs as $point) {
                    $models[] = [
                        'id' => $point->id,
                        'text' => sprintf(
                            '[%s] %s - %s',
                            $point->hash_id,
                            $model->name,
                            $point->name
                        )
                    ];
                }

                return $models;
            }, []);

        $employeesFilterOptions = Employee::all()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'text' => "[$employee->id] " . $employee->name,
            ];
        });

        return view('admin.employees.index')
            ->with([
                'fields' => $fields,
                'currentUserPermissions' => $currentUserPermissions,

                // modal
                'rolesModalOptions' => $rolesModalOptions,
                'pointsModalOptions' => $pointsModalOptions,
                'allPermissions' => $allPermissions,
                'clientRoleId' => $clientRoleId,
                'headOperatorSdpoRoleId' => $headOperatorSdpoRoleId,

                // filter
                'pointsFilterOptions' => $pointsFilterOptions,
                'rolesFilterOptions' => $rolesFilterOptions,
                'employeesFilterOptions' => $employeesFilterOptions,
            ]);
    }
}