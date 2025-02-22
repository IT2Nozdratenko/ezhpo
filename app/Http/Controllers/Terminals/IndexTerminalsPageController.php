<?php

namespace App\Http\Controllers\Terminals;

use App\Enums\DeviceEnum;
use App\FieldPrompt;
use App\Terminal;
use App\Town;
use Illuminate\Support\Facades\Auth;

final class IndexTerminalsPageController
{
    public function __invoke()
    {
        $terminals = Terminal::query()
            ->select([
                'terminals.hash_id',
                'terminal_checks.serial_number as serial_number',
                'terminals.name'
            ])
            ->leftJoin('terminal_checks', 'terminals.id', '=', 'terminal_checks.terminal_id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->hash_id,
                    'text' => sprintf(
                        '[%s] %s %s',
                        $item->hash_id,
                        $item->name,
                        $item->serial_number ? "s/n: " . $item->serial_number : ""
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

        return view('admin.terminals.index')
            ->with([
                'fields' => $fields,
                'devicesOptions' => DeviceEnum::options(),
                'pointsToTable' => $pointsToTable,

                'current_user_permissions' => $currentUserPermissions,

                'terminals' => $terminals,
                'points' => $points,
            ]);
    }
}