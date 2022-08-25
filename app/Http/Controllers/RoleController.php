<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('admin.groups.index')
            ->with([
                'roles' => Role::all(),
                'all_permissions' => \Spatie\Permission\Models\Permission::orderBy('guard_name')->get()
            ]);
    }


    /**
     * Создать
     *
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => ['required', 'string', 'min:1', 'max:255'],
            'guard_name' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors(),
                'status'  => false,
            ]);
        }

        $data = $validator->validated();

        $role = Role::create(Arr::only($data, ['name', 'guard_name']));

        $role->permissions()->sync($request->permissions);

        return response([
            'status' => $role->save(),
        ]);
    }

    /**
     * show one elem
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        return response(Role::with(['permissions'])->find($id));
    }

    /**
     * Редактирование
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->params;

        $role = Role::find($id);
        $role->name = $data['name'];
        $role->guard_name = $data['guard_name'];

        $role->permissions()->sync($data['permissions']);

        return response([
            'status' => $role->save()
        ]);
    }

    /**
     * delete
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        return response([
            'status' => Role::find($id)->delete()
        ]);
    }
}
