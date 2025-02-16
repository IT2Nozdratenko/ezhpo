<?php

namespace App\Http\Controllers;

use App\Actions\Terminal\Update\Dto\TerminalCheckUpdateAction;
use App\Actions\Terminal\Store\Dto\TerminalCheckStoreAction;
use App\Actions\Terminal\Store\Dto\TerminalDeviceStoreAction;
use App\Actions\Terminal\Store\TerminalCheckStoreHandler;
use App\Actions\Terminal\Store\TerminalDeviceStoreHandler;
use App\Actions\Terminal\Store\TerminalStoreHandler;
use App\Actions\Terminal\Update\TerminalCheckUpdateHandler;
use App\Actions\Terminal\Update\TerminalUpdateHandler;
use App\Enums\DeviceEnum;
use App\FieldPrompt;
use App\Models\Forms\Form;
use App\Role;
use App\Services\Terminals\TerminalsToCheckService;
use App\TerminalCheck;
use App\TerminalDevice;
use App\Town;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Throwable;

class TerminalController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('api')) {
            $terminals = User::query()
                ->select([
                    'users.*',
                    'terminal_checks.serial_number',
                    'terminal_checks.date_end_check',
                ])
                ->with([
                    'roles',
                    'pv',
                    'pv.town',
                    'company',
                    'stamp',
                    'terminalDevices',
                    'terminalCheck'
                ])
                ->leftJoin('terminal_checks', 'users.id', '=', 'terminal_checks.user_id')
                ->leftJoin('terminal_settings', 'users.id', '=', 'terminal_settings.terminal_id')
                ->leftJoin('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.role_id', '=', 9);
                })
                ->whereNotNull('model_has_roles.model_id');

            if ($request->get('deleted')) {
                $terminals->with(['deleted_user'])->onlyTrashed();
            }

            if ($pvId = $request->get('point_id')) {
                $terminals->whereIn('users.pv_id', $pvId);
            }

            $settingsFilter = $request->get('settings');
            if ($settingsFilter && $settingsFilter !== 'all') {
                if ($settingsFilter === 'with_settings') {
                    $terminals->whereNotNull('terminal_settings.id');
                }
                if ($settingsFilter === 'without_settings') {
                    $terminals->whereNull('terminal_settings.id');
                }
            }

            if ($companyId = $request->get('company_id')) {
                $terminals->whereIn('users.company_id', $companyId);
            }

            if ($id = $request->get('hash_id')) {
                $terminals->whereIn('users.hash_id', $id);
            }

            if ($townId = $request->get('town_id')) {
                $terminals
                    ->leftJoin('points', 'users.pv_id', '=', 'points.id')
                    ->whereIn('points.pv_id', $townId);
            }

            if ($dateCheck = $request->get('date_check')) {
                $terminals->where('terminal_checks.date_end_check', '>=', Carbon::parse($dateCheck)->startOfDay());
            }

            if ($toDateCheck = $request->input('TO_date_check')) {
                $terminals->where('terminal_checks.date_end_check', '<=', Carbon::parse($toDateCheck)->endOfDay());
            }

            if ($sortBy = $request->get('sortBy', 'id')) {
                $terminals->orderBy($sortBy, $request->get('sortDesc') == 'true' ? 'DESC' : 'ASC');
            }

            $paginate = $terminals->paginate(100);

            $terminals = $paginate->getCollection();

            $forms = Form::query()
                ->select([
                    'forms.created_at',
                    'medic_forms.terminal_id'
                ])
                ->leftJoin('medic_forms', 'forms.uuid', '=', 'medic_forms.forms_uuid')
                ->whereIn('medic_forms.terminal_id', $terminals->pluck('id'))
                ->where('forms.created_at', '>=', Carbon::now()->subMonth()->startOfMonth());

            $startOfMonth = Carbon::now()->startOfMonth();

            foreach ($terminals as $terminal) {
                $terminal->month_amount = $forms
                    ->where('terminal_id', $terminal->id)
                    ->where('created_at', '>=', $startOfMonth)
                    ->count();

                $terminal->last_month_amount = $forms
                    ->where('terminal_id', $terminal->id)
                    ->where('created_at', '<', $startOfMonth)
                    ->count();
            }

            return response([
                'total_rows' => $paginate->total(),
                'current_page' => $paginate->currentPage(),
                'items' => $paginate->getCollection(),
            ]);
        }

        $terminals = User::query()
            ->select([
                'users.hash_id',
                'terminal_checks.serial_number as serial_number',
                'users.name'
            ])
            ->leftJoin('terminal_checks', 'users.id', '=', 'terminal_checks.user_id')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.role_id', '=', 9);
            })
            ->whereNotNull('model_has_roles.model_id')
            ->get()
            ->map(function ($model) {
                return [
                    'id' => $model->hash_id,
                    'text' => sprintf(
                        '[%s] %s %s',
                        $model->hash_id,
                        $model->name,
                        $model->serial_number ? "s/n: " . $model->serial_number : ""
                    )
                ];
            })
            ->toArray();

        $points = Town::query()
            ->with(['pvs'])
            ->orderBy('towns.name')
            ->get();

        $pointsToTable = $points->map(function ($model) {
            $option['label'] = $model->name;

            foreach ($model->pvs as $pv) {
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

        $user = Auth::user();

        $currentUserPermissions = [
            'permission_to_edit' => $user->access('employee_update'),
            'permission_to_view' => $user->access('employee_read'),
            'permission_to_create' => $user->access('employee_create'),
            'permission_to_delete' => $user->access('employee_delete'),
            'permission_to_trash' => $user->access('employee_trash'),
            'permission_to_logs_read' => user()->access('employee_logs_read')
        ];

        $fields = FieldPrompt::query()
            ->where('type', 'terminals')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return view('admin.users.terminal')
            ->with([

                'fields' => $fields,
                'devicesOptions' => DeviceEnum::options(),
                'pointsToTable' => $pointsToTable,

                'current_user_permissions' => $currentUserPermissions,
                'all_permissions' => Permission::query()->orderBy('guard_name')->get(),

                'terminals' => $terminals,
                'points' => $points,

                'roles' => Role::whereNull('deleted_at')->get(),
            ]);
    }

    public function getConnectionStatus(Request $request)
    {
        if (!$request->terminals_id) {
            return;
        }

        $terminals = User::whereIn('id', $request->terminals_id)->select('id', 'last_connection_at')->get();
        foreach ($terminals as $terminal) {
            $terminal->connected = false;
            if ($terminal->last_connection_at) {
                $terminal->connected = Carbon::now()->diffInSeconds($terminal->last_connection_at) < 20;
            }
        }

        return $terminals;
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $terminalStoreHandler = new TerminalStoreHandler();
            $terminalUpdateHandler = new TerminalUpdateHandler();
            $terminalCheckStoreHandler = new TerminalCheckStoreHandler();
            $terminalCheckUpdateHandler = new TerminalCheckUpdateHandler();
            $terminalDeviceStoreHandler = new TerminalDeviceStoreHandler();

            if ($request->input('user_id')) {
                $userId = $request->input('user_id');
                $terminalUpdateHandler->handle($request);
            } else {
                $userId = $terminalStoreHandler->handle($request);
            }

            if (TerminalCheck::where('user_id', '=', $userId)->get()->count()) {
                $terminalCheckUpdateHandler->handle(
                    new TerminalCheckUpdateAction(
                        $userId,
                        $request->input('serial_number'),
                        Carbon::parse($request->input('date_check'))
                    )
                );
            } else {
                $terminalCheckStoreHandler->handle(new TerminalCheckStoreAction(
                    $userId,
                    $request->input('serial_number'),
                    Carbon::parse($request->input('date_check'))
                ));
            }

            // todo(hv): вынести в action
            TerminalDevice::where('user_id', '=', $userId)->forceDelete();
            foreach ($request->input('devices') as $device) {
                $terminalDeviceStoreHandler->handle(new TerminalDeviceStoreAction(
                    $userId,
                    $device['id'],
                    $device['serial_number']
                ));
            }

            $user = User::find($userId);
            $user->roles()->sync([9]);

            DB::commit();

            return response([
                'status' => true,
                'user_info' => User::query()
                    ->with(['company'])
                    ->find($user->id),
            ]);
        } catch (Throwable $exception) {
            DB::rollBack();

            return response([
                'status' => 422,
                'errors' => [$exception->getMessage()]
            ], 422);
        }
    }

    public function terminalsToCheck(TerminalsToCheckService $service)
    {
        $data = $service->getIds();

        return response()->json([
            'less_month' => $data['less_month'],
            'expired' => $data['expired']
        ]);
    }
}
