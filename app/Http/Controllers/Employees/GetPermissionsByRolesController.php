<?php

namespace App\Http\Controllers\Employees;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class GetPermissionsByRolesController
{
    public function __invoke(Request $request)
    {
        $permissions = Role::with(['permissions'])
            ->whereIn('id', $request->get('role_ids', []))
            ->get()
            ->reduce(function ($carry, $role) {
                return $carry->merge($role->permissions);
            }, collect())
            ->unique('id')
            ->pluck('id')
            ->values();

        return response($permissions);
    }
}