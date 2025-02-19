<?php

namespace App\Http\Controllers\Terminals;

use App\Enums\UserEntityType;
use App\Models\Forms\Form;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class GetTerminalsTableItemsController
{
    public function __invoke(Request $request)
    {
        $builder = DB::table('terminals as t')
            ->select([
                't.id',
                't.hash_id',
                't.name',
                't.blocked',
                't.deleted_at',
                // todo: вывод удалившего
                'tc.date_end_check',
                'tc.serial_number',
                'c.name as company_id',
                's.name as stamp_id',
                'p.name as pv',
                'pt.name as town',
                'u.id as user_id',
                'u.timezone',
                'u.api_token',
            ])
            ->leftJoin('points as p', 'p.id', '=', 't.pv_id')
            ->leftJoin('towns as pt', 'p.pv_id', '=', 'pt.id')
            ->leftJoin('stamps as s', 's.id', '=', 't.stamp_id')
            ->leftJoin('terminal_checks as tc', 'tc.terminal_id', '=', 't.id')
            ->leftJoin('users as u', function ($join) {
                $join->on('u.entity_id', '=', 't.id')
                    ->where('u.entity_type', '=', UserEntityType::terminal());
            })
            ->leftJoin('companies as c', 'u.company_id', '=', 'c.id');

        if ($request->get('deleted')) {
            $builder->whereNotNull('t.deleted_at');
        }
        else {
            $builder->whereNull('t.deleted_at');
        }

        $pvIds = $request->get('point_id');
        if ($pvIds) {
            $builder->whereIn('t.pv_id', $pvIds);
        }

        $companyIds = $request->get('company_id');
        if ($companyIds) {
            $builder->whereIn('u.company_id', $companyIds);
        }

        $terminalIds = $request->get('hash_id');
        if ($terminalIds) {
            $builder->whereIn('t.hash_id', $terminalIds);
        }

        $townIds = $request->get('town_id');
        if ($townIds) {
            $builder->whereIn('p.pv_id', $townIds);
        }

        $dateCheck = $request->get('date_check');
        if ($dateCheck) {
            $builder->where('tc.date_end_check', '>=', Carbon::parse($dateCheck)->startOfDay());
        }

        $toDateCheck = $request->input('TO_date_check');
        if ($toDateCheck) {
            $builder->where('tc.date_end_check', '<=', Carbon::parse($toDateCheck)->endOfDay());
        }

        $sortBy = $request->get('sortBy', 'id');
        if ($sortBy) {
            $builder->orderBy($sortBy, $request->get('sortDesc') == 'true' ? 'DESC' : 'ASC');
        }

        $paginate = $builder->paginate(100);

        $terminals = $paginate->getCollection();

        // todo: слишком долго выполняется
//        $forms = Form::query()
//            ->select([
//                'forms.created_at',
//                'medic_forms.terminal_id'
//            ])
//            ->leftJoin('medic_forms', 'forms.uuid', '=', 'medic_forms.forms_uuid')
//            // todo: заменить позже на id
//            ->whereIn('medic_forms.terminal_id', $terminals->pluck('user_id'))
//            ->where('forms.created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
//            ->get();
//
//        $startOfMonth = Carbon::now()->startOfMonth();
//
//        foreach ($terminals as $terminal) {
//            $terminal->month_amount = $forms
//                ->where('terminal_id', $terminal->user_id)
//                ->where('created_at', '>=', $startOfMonth)
//                ->count();
//
//            $terminal->last_month_amount = $forms
//                ->where('terminal_id', $terminal->user_id)
//                ->where('created_at', '<', $startOfMonth)
//                ->count();
//        }

        return response([
            'total_rows' => $paginate->total(),
            'current_page' => $paginate->currentPage(),
            'items' => $terminals,
        ]);

    }
}