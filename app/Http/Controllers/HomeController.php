<?php

namespace App\Http\Controllers;

use App\Anketa;
use App\Enums\FormTypeEnum;
use App\Events\UserActions\ClientActionLogRequest;
use App\Exports\AnketasExport;
use App\FieldPrompt;
use App\Models\Forms\Form;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function SaveCheckedFieldsFilter(Request $request)
    {
        $fields = $request->all();
        $type_ankets = $request->type_ankets;

        unset($fields['_token']);

        session(["fields_$type_ankets" => $fields]);

        return redirect($_SERVER['HTTP_REFERER']);
    }

    //TODO: перенести в AnketasController
    public function index(Request $request)
    {
        $user = Auth::user();

        /**
         * Очистка корзины в очереди на утверждение от СДПО
         */
        $clearPakQueue = $request->input('clear')
            && $user->hasRole('admin')
            && ($request->input('type_anketa') === FormTypeEnum::PAK_QUEUE);

        if ($clearPakQueue) {
            Form::where('type_anketa', FormTypeEnum::PAK_QUEUE)->delete();

            return redirect(route('home', FormTypeEnum::PAK_QUEUE));
        }
        /**
         * Очистка корзины в очереди на утверждение от СДПО
         */

        $formType = $request->type_ankets;
        $validTypeForm = User::$defaultUserJournalByRole[$user->role] ?? FormTypeEnum::MEDIC;
        if (isset(Anketa::$anketsKeys[$formType])) {
            $validTypeForm = $formType;
        }

        $trash = filter_var($request->get('trash', 0), FILTER_VALIDATE_BOOLEAN);

        if ($trash) {
            $forms = Form::onlyTrashed();
        } else {
            $forms = Form::query();
        }

        $formDetailsTable = Form::$relatedTables[$validTypeForm];
        $forms = $forms
            ->join($formDetailsTable, 'forms.uuid', '=', "$formDetailsTable.forms_uuid")
            ->leftJoin('drivers', 'forms.driver_id', '=', 'drivers.hash_id')
            ->leftJoin('companies', 'forms.company_id', '=', 'companies.hash_id')
            ->leftJoin('points', 'forms.point_id', '=', 'points.id')
            ->leftJoin('users', 'forms.user_id', '=', 'users.id');

        if ($trash) {
            $forms = $forms->leftJoin('users as delete_users', 'forms.deleted_id', '=', 'delete_users.id');
        }

        $duplicates = $request->get('duplicates', false);
        if (filter_var($duplicates, FILTER_VALIDATE_BOOLEAN) && $request->has('date') && $request->has('TO_date')) {
            if (! $request->has('date') || ! $request->has('TO_date')) {
                $request->session()->flash('error', 'Не выбран период проведения осмотров');
                $request->request->remove('filter');
            }

            $startDate = Carbon::parse($request->input('date'));
            $endDate = Carbon::parse($request->input('TO_date'))->addDay();

            if ($endDate->diff($startDate)->days > 31) {
                $request->session()->flash('error', 'Выбранный период проведения осмотров превышает 31 день');
                $request->request->remove('filter');
            }

            if (! $request->session()->has('error')) {
                $this->setDuplicatesQuery(
                    $validTypeForm,
                    $forms,
                    $startDate,
                    $endDate
                );
            }
        } else {
            $request->session()->forget('error');
        }

        /**
         * Фильтрация анкет в ЛКК
         */
        if ($user->hasRole('client')) {
            $forms = $forms
                ->where('forms.company_id', $user->company->hash_id)
                ->whereNotNull('forms.date')
                ->where(function ($query) use ($validTypeForm, $user) {
                    $query->where('forms.date', '<=', Carbon::now()->addHours($user->timezone ?? 3));

                    if ($validTypeForm === FormTypeEnum::MEDIC) {
                        $query->orWhere(function ($subQuery) {
                            $subQuery
                                ->whereNotNull('medic_forms.flag_pak')
                                ->where('date', '<=', Carbon::now()->addHours(12));
                        });
                    }
                });

            if ($validTypeForm === FormTypeEnum::TECH) {
                $forms = $forms->whereNotNull('tech_forms.car_id');
            } else {
                $forms = $forms->whereNotNull('forms.driver_id');
            }
        }
        /**
         * Фильтрация анкет в ЛКК
         */

        /**
         * Фильтрация анкет
         */
        $filterActivated = !empty($request->get('filter'));
        $filterParams = $request->except([
            'getCounts',
            'trash',
            'export',
            'exportPrikazPL',
            'exportPrikaz',
            'filter',
            'take',
            'orderBy',
            'orderKey',
            'typePrikaz',
            'page',
            'duplicates',
        ]);

        $filtersToTablesMap = [
            'id' => 'forms.id',
            'company_id' => 'forms.company_id',
            'town_id' => 'points.pv_id',
            'pv_id' => 'forms.point_id',
            'user_id' => 'forms.user_id',
            'car_mark_model' => 'cars.mark_model',
            'driver_id' => 'forms.driver_id',
            'car_id' => 'tech_forms.car_id',
            'period_pl' => "$formDetailsTable.period_pl"
        ];

        if (count($filterParams) > 0 && $filterActivated) {
            foreach ($filterParams as $filterKey => $filterValue) {
                if (array_key_exists($filterKey, $filtersToTablesMap)) {
                    $filterKey = $filtersToTablesMap[$filterKey];
                }

                if ($filterValue === null) continue;

                if ($filterKey == 'TO_date' || $filterKey == 'date') {
                    continue;
                }

                if (is_array($filterValue)) {
                    $filterValue = array_unique(array_values($filterValue));

                    if (count($filterValue) === 1) {
                        $filterValue = $filterValue[0];
                    }
                }

                if ($filterKey === 'hour_from') {
                    $forms->whereTime('forms.date', '>=', $filterValue . ':00');
                    continue;
                }

                if ($filterKey === 'hour_to') {
                    $forms->whereTime('forms.date', '<=', $filterValue . ':00');
                    continue;
                }

                if ($filterKey === 'created_at') {
                    $forms = $forms->where('forms.created_at', '>=', Carbon::parse($filterValue)->startOfDay());
                    continue;
                }

                if ($filterKey === 'TO_created_at') {
                    $forms = $forms->where('forms.created_at', '<=', Carbon::parse($filterValue)->endOfDay());
                    continue;
                }

                if ($filterKey === 'is_dop') {
                    if (!$filterValue) {
                        $compareSign = '<>';
                    } else {
                        $compareSign = '=';
                    }
                    $forms = $forms->where('is_dop', $compareSign, 1);
                    continue;
                }

                if ($filterKey === 'date_prto') {
                    $dateFrom = Carbon::parse($filterValue)->startOfDay();
                    $dateTo = Carbon::parse($filterValue)->endOfDay();
                    $forms = $forms->whereBetween('cars.date_prto', [$dateFrom, $dateTo]);
                    continue;
                }

                if ($filterKey === 'date_prmo') {
                    $dateFrom = Carbon::parse($filterValue)->startOfDay();
                    $dateTo = Carbon::parse($filterValue)->endOfDay();
                    $forms = $forms->whereBetween('drivers.date_prmo', [$dateFrom, $dateTo]);
                    continue;
                }

                if ($filterKey === 'result_dop') {
                    filter_var($filterValue, FILTER_VALIDATE_BOOLEAN)
                        ? $forms->whereNotNull('result_dop')
                        : $forms->whereNull('result_dop');
                    continue;
                }

                if (is_array($filterValue)) {
                    $filterValue = array_map(function ($fvItemValue) {
                        return trim(str_replace('\\', '\\\\', $fvItemValue));
                    }, $filterValue);

                    if ($filterKey === 'flag_pak') {
                        $forms = $forms->where(function ($query) use ($filterValue, $filterKey) {
                            foreach ($filterValue as $flagPakValue) {
                                if ($flagPakValue === 'internal') {
                                    $query = $query->orWhereNull('medic_forms.flag_pak');
                                } else {
                                    $query = $query->orWhere('medic_forms.flag_pak', $flagPakValue);
                                }
                            }

                            return $query;
                        });
                        continue;
                    }

                    if ($filterKey === 'driver_group_risk') {
                        $forms = $forms->where(function ($query) use ($filterValue, $filterKey) {
                            foreach ($filterValue as $fvItemValue) {
                                $query = $query->orWhere('driver_group_risk', 'like', '%' . $fvItemValue . '%');
                            }

                            return $query;
                        });

                        continue;
                    }

                    if ($filterKey === 'car_type_auto') {
                        $forms = $forms->where(function ($query) use ($filterValue) {
                            foreach ($filterValue as $fvItemValue) {
                                $query = $query->orWhere('cars.type_auto', $fvItemValue)
                                    ->orWhere('tech_forms.car_type_auto', $fvItemValue);
                            }

                            return $query;
                        });
                    }

                    $forms = $forms->where(function ($query) use ($filterValue, $filterKey) {
                        foreach ($filterValue as $fvItemValue) {
                            $query = $query->orWhere($filterKey, $fvItemValue);
                        }

                        return $query;
                    });
                } else {
                    $filterValue = trim(str_replace('\\', '\\\\', $filterValue));

                    if ($filterKey === 'flag_pak' && $filterValue === 'internal') {
                        $forms = $forms->whereNull('medic_forms.flag_pak');
                        continue;
                    }

                    $strictFilter = strpos($filterKey, '_id') || $filterKey === 'forms.id';

                    if ($strictFilter) {
                        $forms = $forms->where($filterKey, $filterValue);
                        continue;
                    }

                    $forms = $forms->where($filterKey, 'LIKE', '%' . $filterValue . '%');
                }
            }

            if (($filterParams['date'] ?? null) || ($filterParams['TO_date'] ?? null)) {
                $dateFrom = isset($filterParams['date'])
                    ? Carbon::parse($filterParams['date'])->startOfDay()
                    : Carbon::now()->subYears(10);
                $dateTo = isset($filterParams['TO_date'])
                    ? Carbon::parse($filterParams['TO_date'])->endOfDay()
                    : Carbon::now()->addYears(10);

                if (!in_array($validTypeForm, [FormTypeEnum::MEDIC, FormTypeEnum::TECH])) {
                    $forms = $forms
                        ->whereNotNull('forms.date')
                        ->whereBetween('forms.date', [$dateFrom, $dateTo]);
                } else {
                    $forms = $forms->where(function ($query) use ($formDetailsTable, $validTypeForm, $dateFrom, $dateTo) {
                        $query->where(function ($subQuery) use ($formDetailsTable, $validTypeForm, $dateFrom, $dateTo) {
                            $subQuery
                                ->whereNotNull('forms.date')
                                ->whereBetween('forms.date', [$dateFrom, $dateTo]);
                        })->orWhere(function ($subQuery) use ($formDetailsTable, $dateFrom, $dateTo) {
                            $subQuery
                                ->whereNull('forms.date')
                                ->whereBetween("$formDetailsTable.period_pl", [
                                    $dateFrom->format('Y-m'),
                                    $dateTo->format('Y-m')
                                ]);
                        });
                    });
                }
            }
        }
        /**
         * Фильтрация анкет
         */

        /**
         * <Измеряем количество Авто и Водителей (уникальные ID)>
         */
        if ($filterActivated && $request->input('getCounts')) {
            $formsDistinctQuery = $forms->distinct();

            if ($validTypeForm === FormTypeEnum::TECH) {
                $formsDistinctQuery = $formsDistinctQuery
                    ->leftJoin('cars', 'tech_forms.car_id', '=', 'cars.hash_id');
            }

            $counters = [
                'anketasCountDrivers' => $formsDistinctQuery->count('forms.driver_id'),
                'anketasCountCars' => 0,
                'anketasCountCompany' => $formsDistinctQuery->count('forms.company_id'),
            ];

            if ($validTypeForm === FormTypeEnum::TECH) {
                $counters['anketasCountCars'] = $formsDistinctQuery->count('tech_forms.car_id');
            }

            return response()->json($counters);
        }
        /**
         * <Измеряем количество Авто и Водителей (уникальные ID)>
         */

        $export = $filterActivated && $request->get('export');

        $defaultFieldsToSelect = [
            'forms.*',
            "$formDetailsTable.*",
            'forms.created_at as created_at',
            'forms.updated_at as updated_at',
            'forms.deleted_at as deleted_at',
            'users.name as user_name',
            'drivers.fio as driver_fio',
            'drivers.gender as driver_gender',
            'drivers.year_birthday as driver_year_birthday',
            'points.name as pv_id',
            'companies.name as company_name'
        ];

        if ($trash) {
            $defaultFieldsToSelect[] = 'delete_users.name as deleted_user_name';
        }

        /**
         * Обогащение данных
         */
        if ($validTypeForm === FormTypeEnum::TECH) {
            $forms = $forms
                ->leftJoin('trip_tickets', 'forms.id', '=', 'trip_tickets.tech_form_id')
                ->leftJoin('cars', 'tech_forms.car_id', '=', 'cars.hash_id')
                ->select(array_merge($defaultFieldsToSelect, [
                    DB::raw("COALESCE(cars.type_auto, tech_forms.car_type_auto) as car_type_auto"),
                    'cars.mark_model as car_mark_model',
                    'cars.gos_number as car_gos_number',
                    'cars.date_prto as date_prto',
                    'trip_tickets.id as trip_tickets_id'
                ]));
        } else if (($validTypeForm === FormTypeEnum::MEDIC) && ($export || $user->hasRole('client'))) {
            $forms = $forms
                ->leftJoin('medic_form_normalized_pressures', 'forms.id', '=', 'medic_form_normalized_pressures.form_id')
                ->leftJoin('trip_tickets', 'forms.id', '=', 'trip_tickets.medic_form_id')
                ->select(array_merge($defaultFieldsToSelect, [
                    'drivers.date_prmo as date_prmo',
                    'trip_tickets.id as trip_tickets_id',
                    DB::raw("COALESCE(medic_form_normalized_pressures.pressure, medic_forms.tonometer, NULL) as tonometer")
                ]));
        } else if ($validTypeForm === FormTypeEnum::MEDIC) {
            $forms = $forms
                ->select(array_merge($defaultFieldsToSelect, [
                    'drivers.date_prmo as date_prmo'
                ]));
        } else {
            $forms = $forms
                ->select($defaultFieldsToSelect);
        }
        /**
         * Обогащение данных
         */

        /**
         * Метод фильтрации полей для ЛКК и экспортов
         */
        $filterFields = function (Collection $fields, bool $itemsIsModels = false) use ($user, $validTypeForm) {
            $exclude = [];
            if ($user->hasRole('client')) {
                $exclude = config('fields.client_exclude.' . $validTypeForm) ?? [];
            }
            $exclude[] = 'town_id';

            if (count($exclude) === 0) {
                return $fields;
            }

            if ($itemsIsModels) {
                return $fields->filter(function ($field) use ($exclude) {
                    return !in_array($field->field, $exclude);
                });
            }

            return $fields->filter(function ($title, $field) use ($exclude) {
                return !in_array($field, $exclude);
            });
        };
        /**
         * Метод фильтрации полей для ЛКК
         */

        /**
         * Экспорт журнала
         */
        if ($export) {
            $forms = $forms->orderBy('date');

            //TODO: вот тут нужна проверка на разумные лимиты

            if ($validTypeForm == FormTypeEnum::TECH && $request->get('exportPrikaz')) {
                $fields = $filterFields(collect(Anketa::$fieldsKeys['tech_export_to']));
                $forms = $forms->get();
                $title = 'ЭЖ ПРТО.xlsx';
            } else if ($validTypeForm == FormTypeEnum::TECH && $request->get('exportPrikazPL')) {
                $fields = $filterFields(collect(Anketa::$fieldsKeys['tech_export_pl']));
                $forms = $forms->where(['type_view' => 'Предрейсовый/Предсменный'])->get();
                $title = 'ЭЖ учета ПЛ.xlsx';
            } else if ($validTypeForm === FormTypeEnum::MEDIC && $request->get('exportPrikaz')) {
                $fields = $filterFields(collect(Anketa::$fieldsKeys['medic_export_pl']));
                $forms = $forms->get();
                $title = 'ЭЖ ПРМО.xlsx';
            } else if ($validTypeForm === FormTypeEnum::BDD && $request->get('exportPrikaz')) {
                $fields = $filterFields(collect(Anketa::$fieldsKeys['bdd_export_prikaz']));
                $forms = $forms->with(['user.roles'])->get()->map(function ($form) {
                    $form->user_id = 'Инженер по безопасности дорожного движения';
                    unset($form->user);
                    return $form;
                });
                $title = 'ЭЖ инструктажей БДД.xlsx';
            } else {
                $fields = $filterFields(collect(Anketa::$fieldsKeys[$validTypeForm]));
                $forms = $forms->get();
                $title = 'ЭЖ.xlsx';
            }

            return Excel::download(new AnketasExport($forms, $fields, $request->get('exportPrikaz', false)), $title);
        }
        /**
         * Экспорт журнала
         */

        /**
         * Выбор полей
         */
        $fieldsKeys = Anketa::$fieldsKeys[$validTypeForm];
        if ($trash) {
            $fieldsKeys['deleted_at'] = 'Время удаления';
        }
        if ($user->hasRole('client')) {
            $fieldsKeysToReset = [
                'created_at',
                'is_pak',
                'realy',
                'period_pl',
                'company_id',
                'is_dop',
                'driver_id'
            ];
            foreach ($fieldsKeysToReset as $fieldsKeyToReset) {
                unset($fieldsKeys[$fieldsKeyToReset]);
            }
            $fieldsKeys['id'] = true;
        }
        /**
         * Выбор полей
         */

        /**
         * Получение данных
         */
        $defaultOrderBy = $validTypeForm === FormTypeEnum::PAK_QUEUE ? 'ASC' : 'DESC';
        $orderKey = $request->get('orderKey', 'date');
        $orderBy = $request->get('orderBy', $defaultOrderBy);
        $take = $request->get('take') ?? 100;

        if ($filterActivated || $validTypeForm === FormTypeEnum::PAK_QUEUE) {
            $forms = $forms->orderBy($orderKey, $orderBy);
            if ($orderKey === 'result_dop') {
                $forms = $forms->orderBy( 'is_dop', $orderBy === 'ASC' ? 'DESC' : 'ASC');
            }

            $forms = $forms->paginate($take);
            $formsCountResult = $forms->total();
        } else {
            $forms = [];
            $formsCountResult = 0;
        }
        /**
         * Получение данных
         */

        $formsFields = array_keys($fieldsKeys);

        $currentRole = $validTypeForm;
        if (($validTypeForm === FormTypeEnum::PAK_QUEUE) && $user->hasRole('operator_sdpo')) {
            $currentRole = 'operator_sdpo';
        }

        $fieldPrompts = $filterFields(
            FieldPrompt::query()
                ->where('type', $validTypeForm)
                ->orderBy('sort')
                ->orderBy('id')
                ->whereNotIn('field', ['hour_from', 'hour_to'])
                ->get(),
            true
        );

        event(new ClientActionLogRequest(Auth::user(), $validTypeForm));

        return view('home', [
            'title' => Anketa::$anketsKeys[$validTypeForm],
            'name' => $user->name,
            'ankets' => $forms,
            'filter_activated' => $filterActivated,
            'type_ankets' => $validTypeForm,
            'anketsFields' => $formsFields,
            'anketsFieldsTable' => Anketa::$fieldsKeysTable[$validTypeForm] ?? $formsFields,
            'fieldsKeys' => $fieldsKeys,
            'fieldPrompts' => $fieldPrompts,
            'fieldsGroupFirst' => Anketa::$fieldsGroupFirst[$validTypeForm] ?? [],
            'blockedToExportFields' => Anketa::$blockedToExportFields[$validTypeForm] ?? [],
            'anketasCountResult' => $formsCountResult,
            'typePrikaz' => $request->get('typePrikaz'),
            'currentRole' => $currentRole,
            'take' => $take,
            'orderBy' => $orderBy,
            'orderKey' => $orderKey,
            'queryString' => Arr::query($request->except(['orderKey', 'orderBy']))
        ]);
    }

    public function getFilters(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $formType = $request->type_ankets;

        $validFormType = User::$defaultUserJournalByRole[$user->role] ?? FormTypeEnum::MEDIC;
        if (isset(Anketa::$anketsKeys[$formType])) {
            $validFormType = $formType;
        }

        $fields = Anketa::$fieldsKeys[$validFormType];
        $fieldsGroupFirst = Anketa::$fieldsGroupFirst[$validFormType] ?? [];
        $fieldsKeys = array_keys($fields);

        if (auth()->user()->hasRole('client')) {
            unset($fields['created_at']);
            unset($fields['is_pak']);
        }

        $exclude = config('fields.client_exclude.' . $validFormType) ?? [];

        return view('home_filters', [
            'anketsFields' => $fieldsKeys,
            'type_ankets' => $validFormType,
            'fieldsKeys' => $fields,
            'fieldsGroupFirst' => $fieldsGroupFirst,
            'exclude' => $exclude
        ]);
    }

    /**
     * @throws \Exception
     */
    private function setDuplicatesQuery(string $formType, Builder $builder, $start, $end)
    {
        switch ($formType) {
            case FormTypeEnum::MEDIC:
                $table = 'medic_forms';
                break;
            case FormTypeEnum::TECH:
                $table = 'tech_forms';
                break;
            default:
                throw new \Exception("Проверка дубликатов для типа осмотра - $formType не доступна");
        };

        $duplicates = DB::table($table)
            ->select("$table.day_hash")
            ->join('forms', 'forms.uuid', '=', "$table.forms_uuid")
            ->where('forms.date', '>=', $start->format('Y-m-d'))
            ->where('forms.date', '<', $end->format('Y-m-d'))
            ->whereNotNull("$table.day_hash")
            ->groupBy(["$table.day_hash"])
            ->havingRaw("COUNT($table.day_hash) > 1");

        $builder->whereNotNull("$table.day_hash")
            ->joinSub($duplicates, 'duplicates', function (JoinClause $join) use ($table) {
                $join->on("$table.day_hash", '=', 'duplicates.day_hash');
            })
            ->orderByDesc(DB::raw('DATE(forms.date)'))
            //TODO: точно ли это так? в ТО такая же логика?
            ->orderBy('drivers.fio')
            ->orderBy("$table.type_view");
    }
}
