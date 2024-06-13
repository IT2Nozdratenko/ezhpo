<?php

namespace App\Http\Controllers;

use App\Company;
use App\FieldPrompt;
use App\GenerateHashIdTrait;
use App\Town;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use GenerateHashIdTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $users = User::query()
            ->with([
                'roles' => function ($q) use ($request) {},
                'pv',
                'company'
            ])
            ->where(function ($query) use ($request) {
                $query->whereDoesntHave('roles')
                    ->orWhereHas('roles', function ($q) use ($request) {
                        $q->whereNotIn('roles.id', [3, 6, 9]);
                    });
            });

        if ($request->get('deleted')) {
            $users->with(['deleted_user'])->onlyTrashed();
        }

        if ($id = $request->get('hash_id')) {
            $users->whereIn('hash_id', $id);
        }

        if ($email = $request->get('email')) {
            $users->where('email', 'like', "%$email%");
        }

        if ($pv_id = $request->get('point_id')) {
            $users->where('pv_id', $pv_id);
        }

        if ($sortBy = $request->get('sortBy', 'id')) {
            if ($sortBy == 'roles') {
                $users->join('model_has_roles', 'users.id', 'model_has_roles.model_id')
                    ->join('roles', function ($join) use ($request) {
                        $join->on('model_has_roles.role_id', '=', 'roles.id')
                            ->orderBy('roles.guard_name', $request->get('sortDesc') == 'true' ? 'DESC' : 'ASC');
                    })
                    ->orderBy('roles.guard_name', $request->get('sortDesc') == 'true' ? 'DESC' : 'ASC')//;
                    ->select('users.*', 'guard_name')
                    ->groupBy('users.id');
            } else {
                $users->orderBy($sortBy, $request->get('sortDesc') == 'true' ? 'DESC' : 'ASC');
            }
        }

        if ($role = $request->get('role')) {
            $users->whereHas('roles', function ($q) use ($role) {
                $q->where('id', $role);
            });
        }

        if ($request->get('api')) {
            $res = $users->paginate();

            return response([
                'total_rows' => $res->total(),
                'current_page' => $res->currentPage(),
                'items' => $res->getCollection(),
            ]);
        }

        $fields = FieldPrompt::where('type', 'users')->get();

        $points = Town::query()
            ->with(['pvs'])
            ->orderBy('towns.name')
            ->get();

        $pointsToTable = $points->map(function ($model) {
            $option['label'] = $model->name;

            foreach ($model->pvs as $pv){
                $option['options'][] = [
                    'value' => $pv['id'],
                    'text' => $pv['name']
                ];
            }

            return $option;
        });

        $points = $points
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

        $roles = Role::query()->get();

        $rolesToFilter = $roles
            ->where('name', '!=', 'driver')
            ->map(function ($model) {
                return [
                    'id' => $model->id,
                    'text' => sprintf('[%s] %s', $model->id, $model->guard_name)
                ];
            })
            ->toArray();

        $allPermissions = Permission::orderBy('guard_name')->get();

        $currentUserPermissions = [
            'permission_to_edit' => user()->access('employee_update'),
            'permission_to_view' => user()->access('employee_read'),
            'permission_to_create' => user()->access('employee_create'),
            'permission_to_delete' => user()->access('employee_delete'),
            'permission_to_trash' => user()->access('employee_trash'),
            'permission_to_logs_read' => user()->access('employee_logs_read')
        ];

        return view('admin.users.index')
            ->with([
                'users' => $users->paginate(),
                'fields' => $fields,

                'all_permissions' => $allPermissions,
                'current_user_permissions' => $currentUserPermissions,

                'roles' => $roles,
                'roles_to_filter' => $rolesToFilter,

                'points' => $points,
                'points_to_table' => $pointsToTable
            ]);
    }

    /**
     * Получает данные о пользователе по id
     * */
    public function fetchUserData(Request $request): JsonResponse
    {
        $result = User::query()
            ->with([
                'roles',
                'roles.permissions',
                'permissions',
                'pv',
                'company',
                'points'
            ])
            ->find($request->get('user_id'));

        $result->disable = $result->roles
            ->reduce(function ($carry, $role) {
                return $carry->merge($role->permissions);
            }, collect())
            ->unique('id')
            ->pluck('id')
            ->values();

        $result->permission_user = $result->permissions
            ->pluck('id')
            ->values();

        $result->pvs = $result->points
            ->pluck('id')
            ->values();

        return response()->json($result);
    }

    /**
     * Создаёт/обновляет данные пользователя
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function saveUser(Request $request): JsonResponse
    {
        $userIsClient = array_search(6, $request->get('roles', []));
        if ($userIsClient) {
            $pv = null;
            $company = $request->get('company');
        } else {
            $company = null;
            $pv = $request->get('pv');
        }

        $userId = $request->get('user_id');

        $rules = [
            'password' => [
                'required_without:user_id',
                'nullable',
                'string',
                'min:1',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'min:1',
                'max:255',
                empty($userId)
                    ? Rule::unique('users')
                    : Rule::unique('users')->ignore($userId),
                empty($userId)
                    ? Rule::unique('users', 'login')
                    : Rule::unique('users', 'login')->ignore($userId),
            ],
            'login' => [
                'nullable',
                'string',
                'min:1',
                'max:255',
                empty($userId)
                    ? Rule::unique('users')
                    : Rule::unique('users')->ignore($userId),
            ]
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        if (empty($userId)) {
            $user = new User();

            $validator = function (int $hashId) {
                if (User::where('hash_id', $hashId)->first()) {
                    return false;
                }

                return true;
            };

            $user->hash_id = $this->generateHashId(
                $validator,
                config('app.hash_generator.user.min'),
                config('app.hash_generator.user.max'),
                config('app.hash_generator.user.tries')
            );
        } else {
            $user = User::find($userId);
        }

        if ($password = $request->get('password')) {
            $password = Hash::make($password);
            $apiToken = Hash::make(date('H:i:s') . sha1($password));

            $user->password = $password;
            $user->api_token = $apiToken;
        }

        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->eds = $request->get('eds');
        $user->timezone = $request->get('timezone');
        $user->blocked = $request->get('blocked', 0);
        $user->validity_eds_start = $request->get('validity_eds_start');
        $user->validity_eds_end = $request->get('validity_eds_end');
        $user->login = $request->get('login') ?? $request->get('email');

        $user->save();

        $user->roles()->sync($request->get('roles', []));
        $user->permissions()->sync($request->get('permissions', []));
        $user->points()->sync($request->get('pvs', []));
        $user->company()->associate($company);
        $user->pv()->associate($pv);
        $user->save();

        $user = User::query()
            ->with([
                'roles',
                'permissions',
                'pv'
            ])
            ->find($user->id);

        return response()->json([
            'status' => true,
            'user_info' => $user,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy(Request $request)
    {
        return response([
            'status' => User::find($request->post('id'))->delete(),
        ]);
    }

    public function returnTrash(Request $request)
    {
        return response([
            'status' => User::withTrashed()->find($request->post('id'))->restore(),
        ]);
    }

    public function fetchRoleData(Request $request)
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

    public function fetchCompanies(Request $request)
    {
        $search = $request->get("query", "");

        $companies = Company::whereRaw("LOWER(name) LIKE '%{$search}%'")
            ->limit(50)
            ->get();

        return response($companies);
    }
}
