<?php

namespace App\Http\Controllers;

use App\Enums\FormLogActionTypesEnum;
use App\Enums\FormLogModelTypesEnum;
use App\FieldPrompt;
use App\Models\FormEvent;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FormLogController extends Controller
{
    public function index(): View
    {
        $actionTypes = FormLogActionTypesEnum::options()->toArray();

        $modelTypes = FormEvent::query()
            ->select(['model_type'])
            ->distinct()
            ->get()
            ->pluck('model_type')
            ->map(function ($item) {
                return [
                    'id' => $item,
                    'text' => FormLogModelTypesEnum::label($item)
                ];
            })
            ->toArray();

        $users = User::query()
            ->select([
                'users.id as id',
                DB::raw("CONCAT('[',users.hash_id,'] ',users.name) as text")
            ])
            ->distinct()
            ->leftJoin('form_events', 'form_events.user_id', '=', 'users.id')
            ->whereNotNull('form_events.user_id')
            ->get();

        $fieldPromptsMap = FormLogModelTypesEnum::fieldPromptsTypeMap();

        $fieldPrompts = FieldPrompt::query()
            ->select([
                'field',
                'type',
                'name'
            ])
            ->whereIn('type', array_values($fieldPromptsMap))
            ->get();

        foreach ($fieldPromptsMap as &$value) {
            $value = $fieldPrompts
                ->where('type', $value)
                ->pluck('name', 'field')
                ->toArray();

            $value['trip_ticket_id'] = 'ID ПЛ';
            $value['feedback'] = 'Оценка осмотра';
            $value['deleted_id'] = 'ID удалившего пользователя';
            $value['deleted_at'] = 'Дата и время удаления';
            $value['result_dop'] = 'Статус выданного наперед ПЛ';
        }

        return view(
            'admin.form-logs.index',
            compact('actionTypes', 'modelTypes', 'users', 'fieldPromptsMap')
        );
    }

    public function list(Request $request): JsonResponse
    {
        $data = FormEvent::query()
            ->select([
                'form_events.*',
                'form_events.event_type as type',
                'forms.id as form_id',
                DB::raw("IF(ISNULL(users.hash_id), '-', CONCAT('[', users.hash_id, '] ', users.name)) as user")
            ])
            ->dateFrom($request->input('filter.date_start'))
            ->dateTo($request->input('filter.date_end'))
            ->modelTypes($request->input('filter.models'))
            ->modelId($request->input('filter.id'))
            ->uuid($request->input('filter.uuid'))
            ->userIds($request->input('filter.users'))
            ->actionTypes($request->input('filter.actions'))
            ->leftJoin('users', 'form_events.user_id', '=', 'users.id')
            ->leftJoin('forms', 'form_events.form_uuid', '=', 'forms.uuid')
            ->orderBy('created_at', 'desc')
            ->paginate(
                $request->input('limit', 100),
                ['*'],
                'page',
                $request->input('page', 1)
            );

        return response()->json($data);
    }

    public function listByModel(Request $request): JsonResponse
    {
        $modelType = array_search(
            strtolower($request->input('model', '')),
            FormLogModelTypesEnum::fieldPromptsTypeMap()
        );

        if ($modelType === false || !$request->filled('id')) {
            return response()->json([]);
        }

        $data = FormEvent::query()
            ->select([
                'form_events.*',
                'forms.id',
                DB::raw("IF(ISNULL(users.hash_id), '-', CONCAT('[', users.hash_id, '] ', users.name)) as user")
            ])
            ->modelTypes([$modelType])
            ->modelId($request->input('id'))
            ->leftJoin('users', 'form_events.user_id', '=', 'users.id')
            ->leftJoin('forms', 'form_events.form_uuid', '=', 'forms.uuid')
            ->get();

        return response()->json($data);
    }

    public function listByModelMaps(Request $request): JsonResponse
    {
        $actionTypes = FormLogActionTypesEnum::labels();

        $fieldPrompts = FieldPrompt::query()
            ->select([
                'field',
                'type',
                'name'
            ])
            ->where('type', strtolower($request->input('model')))
            ->get()
            ->pluck('name', 'field')
            ->toArray();

        $fieldPrompts['feedback'] = 'Оценка осмотра';
        $fieldPrompts['deleted_id'] = 'ID удалившего пользователя';
        $fieldPrompts['deleted_at'] = 'Дата и время удаления';

        return response()->json([
            'actionTypes' => $actionTypes,
            'fieldPrompts' => $fieldPrompts
        ]);
    }
}
