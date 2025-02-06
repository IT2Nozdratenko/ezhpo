@extends('layouts.app')

@section('title', $title)
@section('sidebar', 1)

@section('custom-styles')
    <style>
        .table-card {
            max-height: 80vh;
            overflow: hidden;
        }

        .table-card > .card-body {
            overflow: scroll;
            padding: 0 !important;
            margin: 15px !important;
            overscroll-behavior: contain;
        }
    </style>
@endsection

@section('content')
    @include('modals.model-log-modal')
    @include('modals.driver-import-modal')
    @include('modals.car-import-modal')
    @include('modals.export-modal', ['model' => $model])
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        </div>
    @endif

    <!-- Редактирование элемента -->
    <div class="modal fade editor-modal" id="modalEditor" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>

    <!-- Добавление элемента -->
    <div id="elements-modal-add" role="dialog" aria-labelledby="elements-modal-label" aria-hidden="true"
         class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Добавление {{ $popupTitle }}</h4>
                    <button type="button"
                            data-dismiss="modal"
                            aria-label="Close"
                            class="close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <form action="{{ route('addElement', $model) }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Заполните форму внимательно и нажмите кнопку "Добавить"</p>

                        @if($model === 'Product')
                            @include('pages.elements.components.modals.add-product-modal')
                        @else
                            @include('pages.elements.components.modals.add-element-modal')
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-success">Добавить</button>
                        <button type="button" data-dismiss="modal" class="btn btn-sm btn-secondary">Закрыть</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $permissionToCreate = (
            user()->access('drivers_create') && $model == 'Driver'
            || user()->access('cars_create') && $model == 'Car'
            || user()->access('company_create') && $model == 'Company'
            || user()->access('discount_create') && $model == 'Discount'
            || user()->access('service_create') && $model == 'Product'
            || user()->access('briefings_create') && $model == 'Instr'
            || user()->access('city_create') && $model == 'Town'
            || user()->access('requisites_create') && $model == 'Req'
            || user()->access('date_control_create') && $model == 'DDates'
            || user()->access('pv_create') && $model == 'Point'
            || (($model == 'Car' || $model == 'Driver' || $model == 'Company') && user()->access('client_create'))
        ) && !request()->get('deleted');

        $permissionToDelete = (
            user()->access('drivers_delete') && $model == 'Driver'
            || user()->access('cars_delete') && $model == 'Car'
            || user()->access('company_delete') && $model == 'Company'
            || user()->access('discount_delete') && $model == 'Discount'
            || user()->access('service_delete') && $model == 'Product'
            || user()->access('briefings_delete') && $model == 'Instr'
            || user()->access('city_delete') && $model == 'Town'
            || user()->access('requisites_delete') && $model == 'Req'
            || user()->access('date_control_delete') && $model == 'DDates'
            || user()->access('system_delete') && $model == 'Settings'
            || user()->access('pv_delete') && $model == 'Point'
        ) && !request()->get('deleted');

        $permissionToEdit = (
            user()->access('drivers_update') && $model == 'Driver'
            || user()->access('cars_update') && $model == 'Car'
            || user()->access('company_update') && $model == 'Company'
            || user()->access('discount_update') && $model == 'Discount'
            || user()->access('service_update') && $model == 'Product'
            || user()->access('briefings_update') && $model == 'Instr'
            || user()->access('city_update') && $model == 'Town'
            || user()->access('requisites_update') && $model == 'Req'
            || user()->access('date_control_update') && $model == 'DDates'
            || user()->access('system_update') && $model == 'Settings'
            || user()->access('pv_update') && $model == 'Point'
        ) && !request()->get('deleted');

        $permissionToView = (
            user()->access('drivers_read') && $model == 'Driver'
            || user()->access('cars_read') && $model == 'Car'
            || user()->access('company_read') && $model == 'Company'
            || user()->access('discount_read') && $model == 'Discount'
            || user()->access('service_read') && $model == 'Product'
            || user()->access('briefings_read') && $model == 'Instr'
            || user()->access('city_read') && $model == 'Town'
            || user()->access('requisites_read') && $model == 'Req'
            || user()->access('date_control_read') && $model == 'DDates'
            || user()->access('system_read') && $model == 'Settings'
            || user()->access('pv_read') && $model == 'Point'
        );

        $permissionToTrashView = (
            user()->access('drivers_trash_read') && $model == 'Driver'
            || user()->access('cars_trash_read') && $model == 'Car'
            || user()->access('company_trash_read') && $model == 'Company'
            || user()->access('discount_trash_read') && $model == 'Discount'
            || user()->access('service_trash_read') && $model == 'Product'
            || user()->access('briefings_trash_read') && $model == 'Instr'
            || user()->access('system_trash') && $model == 'Settings'
            || user()->access('city_trash_read') && $model == 'Town'
            || user()->access('date_control_trash') && $model == 'DDates'
            || user()->access('requisites_trash_read') && $model == 'Req'
            || user()->access('pv_trash_read') && $model == 'Point'
        );

        $permissionToExport = (
            user()->access('drivers_export') && $model == 'Driver'
            || user()->access('cars_export') && $model == 'Car'
        );

        $permissionToLogsView = (
            user()->access('drivers_logs_read') && $model == 'Driver'
            || user()->access('cars_logs_read') && $model == 'Car'
            || user()->access('company_logs_read') && $model == 'Company'
            || user()->access('service_logs_read') && $model == 'Product'
        );

        $permissionToGenerateMetricLKK = ($model === 'Company' && user()->access('generate_metric_lkk'));
        $permissionToViewContract = user()->access('contract_read');
        $permissionToSyncCompany = ($model === 'Company' && user()->access('company_sync'));

        $date_from_filter = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $date_to_filter = now()->subMonth()->endOfMonth()->format('Y-m-d');
    @endphp
    {{--NAVBAR--}}
    @if(!(count($elements) > $max) || !$max)
        <div class="col-md-12">
            <div class="row bg-light p-2">
                @if($permissionToCreate && !request()->get('deleted'))
                    <div class="m-2">
                        <button type="button" data-toggle="modal" data-target="#elements-modal-add"
                                class="@isset($_GET['continue']) TRIGGER_CLICK @endisset btn btn-sm btn-success">
                            Добавить <i class="fa fa-plus"></i>
                        </button>
                    </div>
                @endif

                @if($permissionToView)
                    @if(!(count($elements) >= $max) || !$max)
                        <div class=" m-2">
                            <button type="button" data-toggle-show="#elements-filters" class="btn btn-sm btn-info">
                                <i class="fa fa-filter"></i> <span class="toggle-title">Показать</span> фильтры
                            </button>
                        </div>
                    @endif
                @endif

                @if($permissionToTrashView)
                    <div class="m-2">
                        @if(!request()->get('deleted'))
                            <a href="?deleted=1" class="btn btn-sm btn-warning">
                                Корзина <i class="fa fa-trash"></i>
                            </a>
                        @else
                            <a href="{{ route('renderElements', ['model' => $model]) }}" class="btn btn-sm btn-warning">
                                Назад <i class="fa fa-trash"></i>
                            </a>
                        @endif
                    </div>
                @endif

                @if($permissionToGenerateMetricLKK)
                    <div class="m-2">
                        <button type="button" data-toggle="modal" data-target="#elementsModalGenerateMetric"
                                class="btn btn-sm btn-secondary">
                            Метрика ЛКК <i class="fa fa-table"></i>
                        </button>
                    </div>
                    @component('modals.metric-modal')@endcomponent
                @endif

                @if($permissionToCreate && $model == 'Driver')
                    <div class="m-2">
                        <button type="button" data-toggle="modal" data-target="#driver-import-modal"
                                class="btn btn-sm btn-success">
                            Импортировать <i class="fa fa-download"></i>
                        </button>
                    </div>
                @endif

                @if($permissionToCreate && $model == 'Car')
                    <div class="m-2">
                        <button type="button" data-toggle="modal" data-target="#car-import-modal"
                                class="btn btn-sm btn-success">
                            Импортировать <i class="fa fa-download"></i>
                        </button>
                    </div>
                @endif

                @if($permissionToExport && ! request()->get('deleted') && $isAdminOrClient)
                    <div class="m-2">
                        <export-element-button export-url="{{ route('exportElement', $model) }}"/>
                    </div>
                @endif

                @if($permissionToExport && ! request()->get('deleted') && ! $isAdminOrClient)
                    <div class="m-2">
                        <button type="button" data-toggle="modal" data-target="#export-modal"
                                class="btn btn-sm btn-success">
                            Экспортировать <i class="fa fa-file-excel-o"></i>
                        </button>
                    </div>
                @endif

                <div class="toggle-hidden col-md-12" id="elements-filters">
                    <form onsubmit="document.querySelector('#page-preloader').classList.remove('hide')" action=""
                          method="GET" class="elements-form-filter">

                        <input type="hidden" name="filter" value="1">
                        @if(request()->get('deleted'))
                            <input type="hidden" name="deleted" value="1">
                        @endif

                        <hr>
                        <div class="row">
                            @foreach($fields as $fk => $fv)
                                @if($fk == 'contract_id' || $fk == 'contracts' || $fk == 'photo')
                                    @continue
                                @elseif(isset($fv['hideFilter']) && $fv['hideFilter'])
                                    @continue
                                @endif

                                @php $fv['multiple'] = true; @endphp

                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div class="form-group">
                                        <label>{{ $fv['label'] }}</label>

                                        @if($model === 'Company' && $fk === 'name')
                                            @include('templates.elements_field', [
                                                'v' => [
                                                    'label' => 'Компания',
                                                    'type' => 'select',
                                                    'values' => 'Company',
                                                    'noRequired' => 1,
                                                    'getFieldKey' => 'name'
                                                ],
                                                'k' => $fk,
                                                'is_required' => '',
                                                'default_value' => request()->get($fk)
                                            ])
                                        @elseif($model === 'Instr' && $fk === 'sort')
                                            <!-- Сортировка доступна только инженеру БДД и Админу -->
                                        @elseif($fv['type'] === 'date' && in_array($model, ['Driver', 'Car', 'Company']))
                                            @include('templates.components.date-range-field', [
                                               'v' => $fv,
                                               'k' => $fk,
                                               'disabled' => false,
                                               'is_required' => '',
                                               'default_value_start' => request()->get($fk . '_start'),
                                               'default_value_end' => request()->get($fk . '_end'),
                                           ])
                                        @elseif($fk === 'pressure_systolic' || $fk === 'pressure_diastolic')
                                            @include('templates.components.pressure-field', [
                                               'v' => $fv,
                                               'k' => $fk,
                                               'disabled' => false,
                                               'is_required' => '',
                                               'default_value_min' => request()->get($fk . '_min'),
                                               'default_value_max' => request()->get($fk . '_max'),
                                           ])
                                        @else
                                            @include('templates.elements_field', [
                                                'v' => $fv,
                                                'k' => $fk,
                                                'disabled' => false,
                                                'is_required' => '',
                                                'default_value' => request()->get($fk),
                                            ])
                                        @endif

                                    </div>
                                </div>
                            @endforeach
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <button type="submit" class="btn btn-sm btn-info">Поиск</button>
                                <a href="?" class="btn btn-sm btn-danger">Сбросить</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @if(!$permissionToView)
        @dump('Нет доступа для просмотра')
    @else
        <div class="card table-card my-4">
            <div class="card-body">
                <table id="elements-table" class="table table-striped table-sm">
                    <thead>
                    <tr>
                        @foreach ($fieldPrompts as $field)
                            @if(($field->field == 'products_id' || $field->field == 'services') && user()->hasRole('client'))
                                @continue
                            @elseif($field->field === 'where_call_name' && !user()->access('companies_access_field_where_call_name'))
                                @continue
                            @elseif($field->field === 'where_call' && !user()->access('companies_access_field_where_call'))
                                @continue
                            @endif
                            <th data-key="{{ $field->field }}">
                            <span class="user-select-none"
                                  @if($field->field == 'comment')
                                      style="width: 200px;"
                                  @endif
                                  @if ($field->content)
                                      data-toggle="tooltip"
                                  data-html="true"
                                  data-trigger="click hover"
                                  title="{{ $field->content }}"
                                @endif
                            >
                                {{ $field->name }}
                            </span>

                                @if(!in_array($field->field, ['contracts', 'contract', 'contract_id']))
                                    <a href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey={{ $field->field }}&{{ $queryString }}">
                                        <i class="fa fa-sort"></i>
                                    </a>
                                @endif
                            </th>
                        @endforeach

                        @if($permissionToLogsView)
                            {{--Логи--}}
                            <th width="60">#</th>
                        @endif

                        @if($permissionToDelete)
                            {{--УДАЛЕНИЕ--}}
                            <th width="60">#</th>
                        @endif

                        @if(request()->get('deleted'))
                            <th width="60">Удаливший</th>
                            <th width="60">Время удаления</th>
                            <th width="60">#</th>
                        @endif

                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($elements as $el)
                        <tr>
                            @foreach ($fieldPrompts as $field)
                                @if($field->field === 'products_id' && user()->hasRole('client'))
                                    @continue
                                @endif
                                @if($field->field === 'services' && user()->hasRole('client'))
                                    @continue
                                @endif
                                @if($field->field === 'where_call_name' && !user()->access('companies_access_field_where_call_name'))
                                    @continue
                                @endif
                                @if($field->field === 'where_call' && !user()->access('companies_access_field_where_call'))
                                    @continue
                                @endif
                                <td class="td-option">
                                    @if($field->field === $editOnField && $permissionToEdit)
                                        <a href="#" class="showEditModal"
                                           data-route="{{ route('showEditElementModal', ['id' => $el->id, 'model' => $model]) }}"
                                           data-toggle="modal" data-target="#modalEditor">
                                    @endif

                                    @if($field->field === 'company_id' && $el->company_id)
                                        <div>
                                            @if(user()->access('company_read'))
                                                <a href="{{ route('renderElements', ['model' => 'Company', 'filter' => 1, 'id' => $el->company_id ]) }}">
                                                    {{ app('App\Company')->getName($el->company_id) }}
                                                </a>
                                            @else
                                                {{ app('App\Company')->getName($el->company_id) }}
                                            @endif
                                            <p>
                                                @if(user()->access('cars_read'))
                                                    <a class="btn btn-sm btn-outline-info"
                                                       href="{{ route('renderElements', ['model' => 'Car', 'filter' => 1, 'company_id' => $el->company_id ]) }}">
                                                        <i class="fa fa-car"></i>
                                                    </a>
                                                @endif
                                                @if(user()->access('drivers_read'))
                                                    <a class="btn btn-sm btn-outline-info"
                                                       href="{{ route('renderElements', ['model' => 'Driver', 'filter' => 1, 'company_id' => $el->company_id ]) }}">
                                                        <i class="fa fa-user"></i>
                                                    </a>
                                                @endif
                                            </p>
                                        </div>
                                    @elseif($field->field === 'active' && $model === 'Instr')
                                        {{ $el[$field->field] == 1 ? 'Да' : 'Нет' }}
                                    @elseif($field->field === 'is_default' && $model === 'Instr')
                                        {{ $el[$field->field] == 1 ? 'Да' : 'Нет' }}
                                    @elseif ($field->field === 'journals')
                                        <nobr>
                                            @if(user()->access('medic_read'))
                                                <a class="btn btn-sm btn-outline-info"
                                                   href="{{ route('home', \App\Enums\FormTypeEnum::MEDIC) }}/?filter=1&{{ $field->type . '_id' }}={{ $el->hash_id }}&date={{ $date_from_filter }}&TO_date={{ $date_to_filter }}">
                                                    МЕД
                                                </a>
                                            @endif
                                            @if(user()->access('tech_read') )
                                                <a class="btn btn-sm btn-outline-info"
                                                   href="{{ route('home', \App\Enums\FormTypeEnum::TECH) }}/?filter=1&{{ $field->type . '_id' }}={{ $el->hash_id }}&date={{ $date_from_filter }}&TO_date={{ $date_to_filter }}">
                                                    ТЕХ
                                                    @endif
                                                </a>
                                        </nobr>
                                    @elseif($field->field === 'reqs_validated' && $model === 'Company')
                                        {{ $el[$field->field] == 1 ? 'Да' : 'Нет' }}
                                    @elseif($field->field === 'one_c_synced' && $model === 'Company')
                                        {{ \App\Enums\OneCSyncStatusEnum::getTitle($el[$field->field]) }}
                                    @elseif ($field->field === 'user_id')
                                        {{ app('App\User')->getName($el->user_id, false) }}
                                    @elseif ($field->field === 'req_id')
                                        {{ app('App\Req')->getName($el->req_id) }}
                                    @elseif ($field->field === 'town_id')
                                        {{ app('App\Town')->getName($el->town_id) }}
                                    @elseif ($field->field === 'pv_id' && $type !== 'Point')
                                        {{ app('App\Point')->getPointText($el->pv_id) }}
                                    @elseif ($field->field === 'pv_id' && $type === 'Point')
                                        {{ app('App\Town')->getName($el->pv_id) }}
                                    @elseif ($field->field === 'products_id')
                                        {{ app('App\Product')->getName($el->products_id) }}
                                    @elseif ($field->field === 'stamp_id')
                                        {{ app('App\Stamp')->getName($el->stamp_id) }}
                                    @elseif ($field->field === 'essence')
                                        {{ app('App\Product')::$essence[$el->essence] ?? ''  }}
                                    @elseif ( $field->field === 'contracts')
                                        @foreach($el->contracts as $contract)
                                            <h3>
                                                @if($permissionToViewContract)
                                                    <a href="/contract?id={{ $contract['id'] }}">
                                                        <span class="badge badge-success">
                                                            {{ $contract['name']  }}
                                                        </span>
                                                    </a>
                                                @else
                                                    <span class="badge badge-success">
                                                        {{ $contract['name']  }}
                                                    </span>
                                                @endif
                                            </h3>
                                        @endforeach
                                    @elseif ($field->field === 'services' && ($model === 'Car' || $model === 'Driver' || $model === 'Company'))
                                        @foreach($el->contracts as $contract)
                                            @foreach($contract->services as $service)
                                                <h5>
                                                    <span class="badge badge-success">
                                                        {{ $service['name']  }}
                                                    </span>
                                                </h5>
                                            @endforeach
                                        @endforeach
                                    @elseif ($field->field === 'document_bdd' || $field->field === 'bitrix_link' || $field->field === 'link_waybill')
                                        <a href="{{ $el[$field->field] }}">{{ $el[$field->field] }}</a>
                                    @elseif ($field->field === 'photo')
                                        @if(Storage::disk('public')->exists($el[$field->field]) && $el[$field->field] !== '<' && $el[$field->field] !== '>')
                                            <a href="{{ Storage::url($el[$field->field]) }}"
                                               data-fancybox="gallery_{{ $el->id }}">
                                                <b>
                                                    <i class="fa fa-camera"></i>
                                                </b>
                                            </a>
                                        @endif
                                    @elseif ($field->field === 'crm')
                                        <nobr>
                                            @if(user()->access('report_service_company_read'))
                                                <a class="btn btn-sm btn-outline-info"
                                                   href="{{ route('report.get', ['type' => 'journal', 'company_id' => $el->hash_id]) }}">
                                                    ₽
                                                </a>
                                            @endif
                                            @if(user()->access('cars_read'))
                                                <a class="btn btn-sm btn-outline-info"
                                                   href="{{ route('renderElements', ['model' => 'Car', 'filter' => 1, 'company_id' => $el->id ]) }}">
                                                    <i class="fa fa-car"></i>
                                                </a>
                                            @endif
                                            @if(user()->access('drivers_read'))
                                                <a class="btn btn-sm btn-outline-info"
                                                   href="{{ route('renderElements', ['model' => 'Driver', 'filter' => 1, 'company_id' => $el->id ]) }}">
                                                    <i class="fa fa-user"></i>
                                                </a>
                                            @endif
                                        </nobr>
                                    @elseif ($field->field === 'pressure_systolic' && $model === 'Driver')
                                        {{ $el->getPressureSystolic() }}
                                    @elseif ($field->field === 'pressure_diastolic' && $model === 'Driver')
                                        {{ $el->getPressureDiastolic() }}
                                    @elseif($field->field === 'driver_license_issued_at')
                                        {{ $el[$field->field] }}
                                    @elseif($field->field === 'date' || strpos($field->field, '_at') > 0)
                                        {{ date('d-m-Y H:i:s', strtotime($el[$field->field])) }}
                                    @elseif($field->field === 'autosync_fields')
                                        @foreach(explode(',', $el[$field->field]) as $aSyncData)
                                            <div class="text-bold text-success"><i
                                                    class="fa fa-refresh"></i> {{ __($aSyncData) }}
                                            </div>
                                        @endforeach
                                    @elseif ($field->field === 'date_of_employment')
                                        {{ $el[$field->field] ? \Carbon\Carbon::parse($el[$field->field])->format('d.m.Y') : '' }}
                                    @elseif ($field->field === 'trigger')
                                        {{ $el[$field->field] === '<' ? 'меньше' : 'больше'  }}
                                    @else
                                        @foreach(explode(',', htmlspecialchars($el[$field->field])) as $keyElK => $valElK)
                                            @if($keyElK !== 0),
                                            @endif
                                            {{ htmlspecialchars_decode(__($valElK)) }}
                                        @endforeach
                                    @endif
                                </td>
                            @endforeach

                            @if($permissionToLogsView)
                                {{--ЛОГИ--}}
                                <td class="td-option">
                                    <button type="button"
                                            data-model-id="{{ $el->id }}"
                                            data-toggle="modal"
                                            data-target="#model-log-modal"
                                            class="btn btn-sm btn-secondary">
                                        <i class="fa fa-book"></i>
                                    </button>
                                </td>
                            @endif

                            @if($permissionToDelete)
                                {{--УДАЛЕНИЕ--}}
                                <td class="td-option">
                                    <a href="{{ route('removeElement', ['type' => $model, 'id' => $el->id ]) }}"
                                       class="ACTION_DELETE btn btn-sm btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            @endif
                            @if(request()->get('deleted'))
                                <td class="td-option">
                                    {{ ($el->deleted_user->name) }}
                                </td>
                                <td class="td-option">
                                    {{ ($el->deleted_at) }}
                                </td>
                                <td class="td-option">
                                    <a href="{{ route('removeElement', ['type' => $model, 'id' => $el->id , 'undo' => 1]) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fa fa-undo"></i>
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @endforeach

                    @if (count($elements) < 1)
                        <tr class="text-center">
                            <td colspan="{{ count($fields)+4 }}">Элементы не найдены</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    @if(count($elements) > 1)
                        {{ $elements->appends($_GET)->render() }}
                    @endif

                    @include('templates.take_form')

                    @if(user()->hasRole('client'))
                        <p>Элементов найдено: {{ method_exists($elements, 'total') ? $elements->total() : '' }}</p>
                    @else
                        <p>Элементов всего: {{ $elements_count_all }}</p>
                        <p>Элементов
                            найдено: {{ method_exists($elements, 'total') ? $elements->total() : $elements_count_all }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @push('setup-scripts')
        <script>
            window.PAGE_SETUP = {
                LOGS_MODAL: {
                    tableDataUrl: '{{ route('logs.list-model') }}',
                    mapDataUrl: '{{ route('logs.list-model-map') }}',
                    model: '{{ $model }}',
                },
                MODEL_SEARCHER: {
                    tableDataUrl: '{{ route('searchElement') }}',
                }
            };
        </script>
    @endpush

    @section('custom-scripts')
        <script>
            @if(count($elements) <= 0)
            setTimeout(function () {
                $('[data-toggle-show*="-filters"]').trigger('click')
            }, 500)
            @endif

            $('#model-log-modal').on('show.bs.modal', function (event) {
                document.dispatchEvent(new CustomEvent(
                    "loadLogsModalData",
                    {
                        detail: {
                            modelId: $(event.relatedTarget).data('model-id'),
                        }
                    }
                ))
            })

            $('#export_company_select').select2({
                dropdownParent: $('#export-modal'),
                placeholder: 'Нажмите для выбора из списка',
                allowClear: true,
                multiple: false,
                width: '100%',
                ajax: {
                    dataType: 'json',
                    url: '{{ route('companies.select') }}',
                    delay: 250,
                    data: (params) => {
                        return {
                            search: params.term || ''
                        }
                    },
                    processResults: (result) => {
                        return {
                            results: result.map((item) => {
                                return {
                                    id: item.id,
                                    text: `[${item.hash_id}] ${item.name}`
                                }
                            })
                        }
                    },
                    cache: true

                }
            });

            $('[data-field-type="date-picker"]').each((index, element) => {
                initDatePicker($(element), {
                    mode: 'single'
                })
            })

            $('.start-date').change(function () {
                const start = $(this).val()
                const end = $('.end-date').val()

                if (!end) {
                    return
                }

                if (end < start) {
                    $('.end-date').val(start)
                }
            })

            $('.end-date').change(function () {
                const start = $('.start-date').val()
                const end = $(this).val()

                if (!start) {
                    return
                }

                if (end < start) {
                    $('.start-date').val(end)
                }
            })

            $('.generate-metric').click(function () {
                const url = '{{ route('generateMetric') }}'
                const start = $('.start-date').val()
                const end = $('.end-date').val()

                if (start && end) {
                    $('.spinner-btn').attr('style', '')
                    $('.generate-metric').attr('style', 'display: none')

                    axios
                        .post(url, {
                            start,
                            end
                        }, {responseType: 'blob'})
                        .then(response => {
                            const {data} = response
                            const url = window.URL.createObjectURL(new Blob([data]));
                            const link = document.createElement('a');
                            link.href = url;
                            link.setAttribute('download', `Метрика ЛКК ${start} - ${end}.xlsx`);
                            document.body.appendChild(link);
                            link.click();
                        })
                        .finally(() => {
                            $('.spinner-btn').attr('style', 'display: none')
                            $('.generate-metric').attr('style', '')
                        })
                }
            })

            $(document).ready(function() {
                $('#elements-modal-add input[name="phone"]').on('input', function() {
                    validatePhone(this)
                })

                $('#modalEditor').on('input', 'input[name="phone"]', function() {
                    validatePhone(this)
                })

                function validatePhone(input) {
                    input.value = input.value.replace(/[^+\-\d\s]/g, '')

                    if (input.value.length > 5) {
                        input.value = input.value.slice(0, 18)
                    }
                }
            })

        </script>
    @endsection
@endsection
