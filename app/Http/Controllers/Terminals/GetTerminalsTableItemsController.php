<?php

namespace App\Http\Controllers\Terminals;

use App\Models\Forms\Form;
use App\Terminal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class GetTerminalsTableItemsController
{
    public function __invoke(Request $request)
    {
        $builder = Terminal::query()
            ->with([
                'pv',
                'pv.town',
                'stamp',
                'terminalCheck',
                'user' => function ($query) {
                    return $query->withTrashed();
                },
                'user.company',
            ]);

        if ($request->get('deleted')) {
            $builder->with(['whoDeleted'])->onlyTrashed();
        }

        $pvIds = $request->get('point_id');
        if ($pvIds) {
            $builder->whereHas('pv', function ($query) use ($pvIds) {
                $query->whereIn('id', $pvIds);
            });
        }

        $companyIds = $request->get('company_id');
        if ($companyIds) {
            $builder->whereHas('user.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });
        }

        $terminalIds = $request->get('hash_id');
        if ($terminalIds) {
            $builder->whereIn('hash_id', $terminalIds);
        }

        $townIds = $request->get('town_id');
        if ($townIds) {
            $builder->whereHas('pv.town', function ($query) use ($townIds) {
                $query->whereIn('id', $townIds);
            });
        }

        $dateCheck = $request->get('date_check');
        if ($dateCheck) {
            $builder->whereHas('terminalCheck', function ($query) use ($dateCheck) {
                $query->where('date_end_check', '>=', Carbon::parse($dateCheck)->startOfDay());
            });
        }

        $toDateCheck = $request->input('TO_date_check');
        if ($toDateCheck) {
            $builder->whereHas('terminalCheck', function ($query) use ($toDateCheck) {
                $query->where('date_end_check', '<=', Carbon::parse($toDateCheck)->endOfDay());
            });
        }

        $orderBy = $request->get('sortBy', 'id');
        $orderDirection = filter_var($request->get('sortDesc'),FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        if ($orderBy) {
            $terminalColumns = ['hash_id', 'name', 'blocked', 'stamp_id'];
            $userColumns = ['timezone', 'api_token', 'company_id'];
            $terminalCheckColumns = ['date_end_check', 'serial_number'];

            if (in_array($orderBy, $terminalColumns)) {
                $builder->orderBy($orderBy, $orderDirection);
            }

            if (in_array($orderBy, $userColumns)) {
                $builder
                    ->join('users', 'users.id', '=', 'terminals.related_user_id')
                    ->select('terminals.*', 'users.' . $orderBy)
                    ->orderBy("users.$orderBy", $orderDirection)
                    ->groupBy(['terminals.id']);
            }

            if (in_array($orderBy, $terminalCheckColumns)) {
                $builder
                    ->join('terminal_checks', 'terminal_checks.terminal_id', '=', 'terminals.id')
                    ->select('terminals.*', 'terminal_checks.' . $orderBy)
                    ->orderBy("terminal_checks.$orderBy", $orderDirection)
                    ->groupBy(['terminals.id']);
            }
        }

        $paginator = $builder->paginate(100);

        $terminals = $paginator
            ->getCollection()
            ->map(function (Terminal $terminal) {
                $whoDeleted = null;
                if ($terminal->whoDeleted) {
                    $whoDeleted = $terminal->whoDeleted->getEntityName();
                }

                $deletedAt = null;
                if ($terminal->deleted_at) {
                    $deletedAt = $terminal->deleted_at->format('Y-m-d H:i:s');
                }

                $townName = null;
                if ($terminal->pv && $terminal->pv->town) {
                    $townName = $terminal->pv->town->name;
                }

                $companyName = null;
                if ($terminal->user && $terminal->user->company) {
                    $companyName = $terminal->user->company->name;
                }

                $dateEndCheck = null;
                if ($terminal->terminalCheck) {
                    $dateEndCheck = $terminal->terminalCheck->date_end_check;
                }

                return [
                    'id' => $terminal->id,
                    'hash_id' => $terminal->hash_id,
                    'name' => $terminal->name,
                    'blocked' => $terminal->blocked,
                    'api_token' => $terminal->user->api_token,
                    'timezone' => $terminal->user->timezone,
                    'serial_number' => $terminal->terminalCheck ? $terminal->terminalCheck->serial_number : null,
                    'pv' => $terminal->pv ? $terminal->pv->name : null,
                    'town' => $townName,
                    'company_id' => $companyName,
                    'stamp_id' => $terminal->stamp ? $terminal->stamp->name : null,
                    'deleted' => $deletedAt,
                    'who_deleted' => $whoDeleted,
                    'deleted_at' => $deletedAt,
                    'date_end_check' => $dateEndCheck,
                    'user_id' => $terminal->related_user_id,
                ];
            });

        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');

        $forms = Form::query()
            ->select([
                'medic_forms.terminal_id',
                DB::raw("count(case when forms.created_at >= '$startOfMonth' then 1 end) as month_amount"),
                DB::raw("count(case when forms.created_at < '$startOfMonth' then 1 end) as last_month_amount")
            ])
            ->leftJoin('medic_forms', 'forms.uuid', '=', 'medic_forms.forms_uuid')
            // todo: заменить на id терминала
            ->whereIn('medic_forms.terminal_id', $terminals->pluck('user_id'))
            ->where('forms.created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
            ->groupBy(['medic_forms.terminal_id'])
            ->get();

        $terminals = $terminals->map(function (array $terminal) use ($forms) {
            $formData = $forms->firstWhere('terminal_id', $terminal['user_id']);

            return array_merge($terminal, [
                'month_amount' => $formData->month_amount ?? 0,
                'last_month_amount' => $formData->last_month_amount ?? 0,
            ]);
        });

        return response([
            'total_rows' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'items' => $terminals,
        ]);

    }
}