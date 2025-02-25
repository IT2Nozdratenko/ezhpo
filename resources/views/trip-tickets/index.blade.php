@extends('layouts.app')

@section('title', 'Реестр путевых листов')
@section('sidebar', 1)
@section('class-page', 'trip-tickets-page')

@section('custom-styles')
    <style>
        .hv-checkbox-mass-deletion {
            accent-color: #138496;
            cursor: pointer;
            width: 20px;
            height: 20px;
        }

        .hv-mass-deletion-alert-error {
            font-size: 12px;
        }

        .hv-mass-deletion-alert-error code {
            font-size: 13px;
            border-radius: 3px;
            background-color: #f4b2b0;
            padding: .21rem .4rem;
        }

        #hv-alert-error-close {
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        thead {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 10
        }

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

        .select2-search--inline {
            width: 100%;
        }

        .select2-search__field:placeholder-shown {
            width: 100% !important;
        }

        .show {
            display: block !important;
        }

        .dropleft .dropdown-toggle::before {
            display: none;
        }
    </style>
@endsection

@section('custom-scripts')
    <script type="text/javascript">
        @if (user()->fields_visible)
        let fieldsVisible = {!! user()->fields_visible !!};
        @else
        let fieldsVisible = @json(config('fields.visible'));
        @endif

        const type = 'trip_tickets'

        function setVisibleInputs() {
            $('.ankets-form input').each(function () {
                const name = $(this).attr('name');
                let checked = false;
                if (fieldsVisible[type] && fieldsVisible[type][name]) {
                    checked = true;
                }
                $(this).prop("checked", checked);
                $(this).trigger('change');
            });
        }

        $(document).ready(function () {
            $('#preloader-div').attr('style', 'display: none')
            $('#filters-div').attr('style', '')

            setVisibleInputs();

            const showTableData = el => {
                if (el) {
                    const id = el.attr('name');
                    const prop_checked = el.prop('checked');

                    const tripTicketsTable = $(`.trip-tickets-table thead th[data-field-key="${id}"], .trip-tickets-table tbody tr td[data-field-key="${id}"]`)
                    const displayProp = !prop_checked ? 'none' : 'table-cell'

                    tripTicketsTable.attr('hidden', !prop_checked).css({'display': displayProp})

                    return
                }

                $('.ankets-form input').each(function () {
                    if (this.name !== '_token') {
                        showTableData($(this))
                    }
                })
            }

            showTableData()

            $('.ankets-form input').change(e => {
                let el = $(e.target)
                const id = el.attr('name');

                if (!fieldsVisible[type]) {
                    fieldsVisible[type] = {};
                }

                fieldsVisible[type][id] = el.prop('checked');

                showTableData(el)
            });

            $('#saveFieldsBtn').click(async function () {
                await saveFieldsVisible(fieldsVisible);
                $('.toast-save-checks').toast('show');
            });

            $('#resetFieldsBtn').click(async function () {
                fieldsVisible = JSON.parse(`{!! json_encode(config('fields.visible')) !!}`);

                setVisibleInputs();

                await saveFieldsVisible(null);
                $('.toast-reset-checks').toast('show');
            });

            $('.delete').on('click', function (e) {
                e.preventDefault();
                const link = $(this).attr('href');

                const action = $(this).data('action');

                if (action === 0) {
                    window.location.href = link;

                    return;
                }

                swal.fire({
                    title: 'Подтверждение',
                    text: 'Вы уверены, что хотите удалить ПЛ? Связь с МО и ТО будет удалена!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Да',
                    cancelButtonText: 'Нет'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = link;
                    }
                });
            });
        });

        function saveFieldsVisible(params) {
            return axios.post('/api/fields/visible', {params}, {
                headers: {
                    Authorization: 'Bearer ' + API_TOKEN
                },
            });
        }
    </script>

    <script type="text/javascript">
        const tripTicketsApi = {
            massTrash: '{{ route('trip-tickets.mass-trash') }}',
            print: '{{ route('trip-tickets.print') }}',
            massPrint: '{{ route('trip-tickets.mass-print') }}',
        }
        const data = {
            items: [],
            total: 0
        }

        function updateTripTicketsCheckbox() {
            const tripTicketsStorage = getTripTicketsStorage()

            $('.hv-checkbox-mass-deletion').prop('checked', false)
            tripTicketsStorage.items.forEach(function (item) {
                $(`.hv-checkbox-mass-deletion[data-id="${item}"]`).prop('checked', true)
            })
        }

        function getTripTicketsStorage() {
            if (data === null) {
                return {
                    items: [],
                    total: 0
                }
            }

            return data
        }

        function setTripTicketsStorage(value) {
            data.items = value.items
            data.total = value.total
        }

        function pronunciationWithNumber(number, one, two, eleven) {
            const lastTwo = Math.abs(number) % 100
            const lastOne = Math.abs(number) % 10

            if (lastTwo > 10 && lastTwo < 20) {
                return eleven
            }

            if (lastOne > 1 && lastOne < 5) {
                return two
            }

            if (lastOne === 1) {
                return one
            }

            return eleven
        }

        function updateTripTicketsControl() {
            const control = $('#selected-items-control')
            const controlBtnDelete = $('#selected-items-control-btn-delete')

            const tripTicketsStorage = getTripTicketsStorage()

            if (tripTicketsStorage.total) {
                const records = pronunciationWithNumber(tripTicketsStorage.total, 'путевой лист', 'путевых листа', 'путевых листов')
                const label = "Удалить " + tripTicketsStorage.total + " " + records

                controlBtnDelete.html(label)
                control.addClass('d-flex')
                control.removeClass('d-none')
            } else {
                control.addClass('d-none')
                control.removeClass('d-flex')
            }
        }

        function clearTripTicketsStorage() {
            data.items = []
            data.total = 0
        }

        function pushTripTicketToStorage(id) {
            const tripTicketsStorage = getTripTicketsStorage()

            if (tripTicketsStorage.items.filter(item => item === id).length) {
                return
            }

            tripTicketsStorage.total++
            tripTicketsStorage.items.push(id)

            setTripTicketsStorage(tripTicketsStorage)
        }

        function removeTripTicketFromStorage(id) {
            const tripTicketsStorage = getTripTicketsStorage()

            tripTicketsStorage.items = tripTicketsStorage.items.filter(item => item !== id)
            tripTicketsStorage.total = tripTicketsStorage.items.length

            setTripTicketsStorage(tripTicketsStorage)
        }

        $(document).ready(function () {
            clearTripTicketsStorage()
            updateTripTicketsControl()
            updateTripTicketsCheckbox()

            $('.hv-checkbox-mass-deletion').click(function () {
                const id = $(this).attr('data-id')
                const checked = $(this).is(':checked')

                if (checked) {
                    pushTripTicketToStorage(id)
                } else {
                    removeTripTicketFromStorage(id)
                }

                updateTripTicketsControl()
            })

            $('#selected-items-control-btn-unset').click(function () {
                clearTripTicketsStorage()
                updateTripTicketsControl()
                updateTripTicketsCheckbox()
            })

            $('#selected-items-control-btn-delete').click(function () {
                const tripTicketsStorage = getTripTicketsStorage()

                axios
                    .get(tripTicketsApi.massTrash, {
                        params: {
                            action: '{{ request()->get('trash') ? 0 : 1 }}',
                            ids: tripTicketsStorage.items
                        }
                    })
                    .then(() => {
                        clearTripTicketsStorage()
                        window.location.reload()
                    })
                    .catch(() => {
                    })
            })

            $('#select-all').click(function () {
                $('.trip-tickets-table input[type="checkbox"]').each(function () {
                    if (!$(this).prop('checked')) {
                        $(this).click();
                    }
                });
            })

            $('.hv-btn-trash').click(function (e) {
                e.stopPropagation()
                const id = $(this).attr('data-id')

                removeTripTicketFromStorage(id)
                updateTripTicketsControl()
            })

            const downloadExcelFileToPrint = (url, params) => {
                axios({
                    method: 'post',
                    url: url,
                    data: params,
                    responseType: 'blob',
                })
                    .then((response) => {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');

                        let filename = 'Путевой лист.xlsx';
                        const contentDisposition = response.headers['content-disposition'];
                        if (contentDisposition && contentDisposition.includes('filename=')) {
                            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            const matches = filenameRegex.exec(contentDisposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, ''); // Remove quotes
                            }
                        }

                        link.href = url;
                        link.setAttribute('download', filename);

                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    })
                    .catch((error) => {
                        if (error.response && error.response.data instanceof Blob) {
                            const blob = error.response.data;

                            blob.text().then((text) => {
                                try {
                                    const errorData = JSON.parse(text);
                                    let message = errorData.error ?? '';

                                    swal.fire({
                                        title: 'При формировании файла произошла ошибка',
                                        text: message,
                                        icon: 'error'
                                    });
                                } catch (e) {
                                    swal.fire({
                                        title: 'При формировании файла произошла ошибка',
                                        text: text,
                                        icon: 'error'
                                    });
                                }
                            });
                        } else {
                            swal.fire({
                                title: 'При формировании файла произошла ошибка',
                                icon: 'error'
                            });
                        }
                    });
            }

            $('.download-excel-to-print-btn').click((event) => {
                const btn = $(event.currentTarget)
                const uuid = btn.attr('data-uuid')

                downloadExcelFileToPrint(tripTicketsApi.print, {
                    id: uuid
                })
            })

            $('#mass-download-excel-to-print-btn').click(function () {
                const tripTicketsStorage = getTripTicketsStorage()

                downloadExcelFileToPrint(tripTicketsApi.massPrint, {
                    ids: tripTicketsStorage.items
                })
            })

            $('#hv-alert-error-close').click(function () {
                $('#hv-alert-error').addClass('d-none')
                $('#hv-alert-error').removeClass('d-flex')
            })
        })
    </script>
@endsection

@php
    $permissionToView = user()->access('trip_tickets_read');
    $permissionToTrashView = user()->access('trip_tickets_trash');
    $permissionToCreateMedicForm = user()->access('trip_tickets_create_medic_form');
    $permissionToCreateTechForm = user()->access('trip_tickets_create_tech_form');
    $permissionToEditMedicForm = user()->access('medic_update');
    $permissionToEditTechForm = user()->access('tech_update');
    $permissionToDelete = user()->access('trip_tickets_delete');
    $permissionToUpdate = user()->access('trip_tickets_edit');
    $permissionToExport = user()->access('trip_tickets_export');
    $permissionToExportPrikaz = user()->access('trip_tickets_export_prikaz');
    $permissionToPrintTripTickets = user()->access('trip_tickets_print_trip_ticket');
    $notDeletedItems = session('not_deleted_items');
@endphp

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    @if(session()->has('success'))
                        <div class="alert alert-success" role="alert">{{ session()->get('success') }}</div>
                    @endif
                </div>

                <div>
                    <div class="col-md-12">
                        <div class="row bg-light p-2">
                            <div class="col-md-6">
                                @if (!user()->hasRole('client'))
                                    <button type="button" data-toggle-show="#ankets-filters"
                                            class="btn btn-sm btn-info"><i class="fa fa-cog"></i> <span
                                            class="toggle-title">Настроить</span> колонки
                                    </button>
                                @endif

                                @if($permissionToTrashView)
                                    @if(request()->get('trash', 0))
                                        <a href="{{ route('trip-tickets.index') }}"
                                           class="btn btn-sm btn-warning">Назад</a>
                                    @else
                                        <a href="?trash=1" class="btn btn-sm btn-warning">Корзина <i
                                                class="fa fa-trash"></i></a>
                                    @endisset
                                @endif
                            </div>
                            <div class="col-md-6 text-right">
                                @if($permissionToExport)
                                    <a href="{{ route('trip-tickets.table-export', request()->query()) }}" class="btn btn-sm btn-default">Экспорт
                                        таблицы <i class="fa fa-download"></i></a>
                                @endif
                                @if($permissionToExportPrikaz)
                                    <a href="{{ route('trip-tickets.table-export', array_merge(request()->query(), ['exportPrikaz' => true])) }}"
                                       class="btn btn-sm btn-default">Экспорт таблицы по приказу <i
                                            class="fa fa-download"></i></a>
                                @endif
                            </div>

                            @if (!user()->hasRole('client'))
                                <div class="toggle-hidden p-3" id="ankets-filters">
                                    <form class="ankets-form">
                                        @foreach($fieldPrompts as $key => $field)
                                            <label>
                                                <input
                                                    checked
                                                    type="checkbox" name="{{ $field->field }}"
                                                    data-value="{{ $key+1 }}"/>
                                                {{ $field->name }} &nbsp;
                                            </label>
                                        @endforeach
                                    </form>
                                    <button class="btn btn-success btn-sm mt-3" id="saveFieldsBtn">Сохранить
                                    </button>
                                    <button class="btn btn-danger btn-sm mt-3" id="resetFieldsBtn">Сбросить</button>
                                    <div class="toast mt-2 toast-save-checks position-absolute">
                                        <div class="toast-body bg-success text-white">
                                            Успешно сохранено
                                        </div>
                                    </div>

                                    <div class="toast mt-2 toast-reset-checks position-absolute">
                                        <div class="toast-body bg-danger text-white">
                                            Успешно сброшено
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="text-center" id="preloader-div">
                        <img src="{{ asset('images/loader.gif') }}" width="30" class="mb-4"/>
                    </div>

                    <div id="filters-div" style="display: none">
                        <div class="col-md-12 pl-3 pr-3 mt-2">
                            <i class="fa fa-filter"></i> Фильтры
                        </div>
                        @if($permissionToView)
                            @component('trip-tickets.components.filters')
                            @endcomponent
                        @endif
                    </div>

                    @if(count($tripTickets) > 0 && $permissionToView)
                        <div id="selected-items-control" class="d-none align-items-center mt-4 mb-2 pl-3 pr-3">
                            @if($permissionToDelete)
                                <button id="selected-items-control-btn-delete" class="btn btn-danger btn-sm"></button>
                            @endif
                            @if($permissionToPrintTripTickets)
                                <button id="mass-download-excel-to-print-btn" class="btn btn-success btn-sm ml-2">Печать ПЛ</button>
                            @endif
                            <button id="select-all" class="btn btn-success btn-sm ml-2">Выделить все на странице
                            </button>
                            <button id="selected-items-control-btn-unset" class="btn btn-success btn-sm ml-2">
                                Снять выделение
                            </button>
                        </div>
                    @endif

                    @if($notDeletedItems)
                        <div id="hv-alert-error"
                             class="alert alert-danger hv-mass-deletion-alert-error d-flex justify-content-between align-items-center pl-3 pr-3">
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <div>Не удалось удалить ПЛ с ID:</div>
                                <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                                    @foreach($notDeletedItems as $item)
                                        <code>{{ $item }}</code>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <div id="hv-alert-error-close">
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($errors))
                        @foreach($errors as $error)
                            <div class="alert alert-danger" role="alert">{{ $error }}</div>
                        @endforeach
                    @endif

                    @if(count($tripTickets) > 0)
                    <hr>
                    <div class="d-flex justify-content-center">
                        {{ $tripTickets->appends($_GET)->render() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-body">
                @if((count($tripTickets) > 0) && $permissionToView)
                    <table id="trip-tickets-table" class="trip-tickets-table table table-striped table-sm" style="min-height: 170px">
                        <thead>
                        <tr>
                            <th>#</th>

                            @foreach($fieldPrompts as $field)
                                <th data-field-key="{{ $field->field }}"
                                    @isset($blockedToExportFields[$field->field])
                                        class="not-export"
                                    @endisset>
                                    <span class="user-select-none"
                                          @if ($field->content)
                                              data-toggle="tooltip"
                                          data-html="true"
                                          data-trigger="click hover"
                                          title="{{ $field->content }}"
                                          @endif>
                                        {{ $field->name }}
                                    </span>
                                    <a class="not-export"
                                       href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey={{ $field->field }}&{{ $queryString }}">
                                        <i class="fa fa-sort"></i>
                                    </a>
                                </th>
                            @endforeach

                            @if(request()->get('trash'))
                                <th width="60">Удаливший</th>
                                <th width="60">Время удаления</th>
                            @endif
                            <th>#</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($tripTickets as $tripTicketKey => $tripTicket)
                            <tr data-field="{{ $tripTicketKey }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        data-id="{{ $tripTicket->uuid }}"
                                        class="hv-checkbox-mass-deletion">
                                </td>

                                @foreach($fieldPrompts as $field)
                                    <td data-field-key="{{ $field->field }}"
                                        @isset($blockedToExportFields[$field->field])
                                            class="not-export"
                                        @endisset>
                                        @if(in_array($field->field, ['start_date', 'created_at']) && $tripTicket[$field->field])
                                            {{ date('d.m.Y', strtotime($tripTicket[$field->field])) }}
                                        @elseif($field->field === 'period_pl' && $tripTicket[$field->field])
                                            {{ date('m.Y', strtotime($tripTicket[$field->field])) }}
                                        @elseif($field->field === 'driver_fio' && user()->access('drivers_read'))
                                            <a href="{{ route('renderElements', ['model' => 'Driver', 'filter' => 1, 'fio' => $tripTicket[$field->field] ]) }}">
                                                {{ $tripTicket[$field->field] }}
                                            </a>
                                        @elseif($field->field === 'car_gos_number' && user()->access('cars_read'))
                                            <a href="{{ route('renderElements', ['model' => 'Car', 'filter' => 1, 'gos_number' => $tripTicket[$field->field] ]) }}">
                                                {{ $tripTicket[$field->field] }}
                                            </a>
                                        @elseif($field->field === 'logistics_method')
                                            {{ \App\Enums\LogisticsMethodEnum::getLabel($tripTicket[$field->field]) }}
                                        @elseif($field->field === 'transportation_type')
                                            {{ \App\Enums\TransportationTypeEnum::getLabel($tripTicket[$field->field]) }}
                                        @elseif($field->field === 'template_code')
                                            {{ \App\Enums\TripTicketTemplateEnum::getLabel($tripTicket[$field->field]) }}
                                        @else
                                            {{ $tripTicket[$field->field] }}
                                        @endif
                                    </td>
                                @endforeach

                                @if($permissionToDelete && request()->get('trash'))
                                    <td class="td-option">
                                        {{ ($tripTicket->deleted_user_name) }}
                                    </td>
                                    <td class="td-option">
                                        {{ ($tripTicket->deleted_at) }}
                                    </td>
                                @endif

                                <td class="td-option not-export dropleft d-flex" style="width: 40px">
                                    <a class="dropdown-toggle" type="button" data-toggle="dropdown"
                                       aria-expanded="false" style="font-size: 1.5rem">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        @if($permissionToUpdate)
                                            <a href="{{ route('trip-tickets.edit', $tripTicket->uuid) }}"
                                               class="dropdown-item"><i class="fa fa-edit"></i>ПЛ</a>
                                        @endif
                                        @if($permissionToEditMedicForm && $tripTicket['medic_form_id'])
                                            <a href="{{ route('forms.get', $tripTicket->medic_form_id) }}"
                                               class="dropdown-item"><i class="fa fa-edit"></i>МО</a>
                                        @endif
                                        @if($permissionToEditTechForm && $tripTicket['tech_form_id'])
                                            <a href="{{ route('forms.get', $tripTicket->tech_form_id) }}"
                                               class="dropdown-item"><i class="fa fa-edit"></i>ТО</a>
                                        @endif

                                        @if($permissionToCreateMedicForm && ! $tripTicket['medic_form_id'])
                                            <a href="{{ route('trip-tickets.create-form', ['id' => $tripTicket->uuid, 'type' => \App\Enums\FormTypeEnum::MEDIC]) }}" class="dropdown-item">
                                                <i class="fa fa-plus"></i> Добавить МО</a>
                                        @endif
                                        @if($permissionToCreateTechForm && ! $tripTicket['tech_form_id'])
                                            <a href="{{ route('trip-tickets.create-form', ['id' => $tripTicket->uuid, 'type' => \App\Enums\FormTypeEnum::TECH]) }}" class="dropdown-item">
                                                <i class="fa fa-plus"></i> Добавить ТО</a>
                                        @endif

                                        {{-- TODO: добавить кнопки "связывания" и "отвязывания" осмотров --}}

                                        @if($permissionToPrintTripTickets)
                                            <a class="dropdown-item download-excel-to-print-btn"
                                               data-uuid="{{ $tripTicket->uuid }}" style="cursor: pointer">
                                                <i class="fa fa-file-excel-o"></i> Печать ПЛ
                                            </a>
                                        @endif

                                        @if($permissionToDelete)
                                            <a
                                                href="{{ route('trip-tickets.trash', ['id' => $tripTicket->uuid, 'action' => request()->get('trash') ? 0 : 1]) }}"
                                                class="hv-btn-trash dropdown-item delete"
                                                data-id="{{ $tripTicket->id }}"
                                                data-action="{{ request()->get('trash') ? 0 : 1 }}">
                                                @if(request()->get('trash', 0))
                                                    <i class="fa fa-undo"></i> Восстановить
                                                @else
                                                    <i class="fa fa-trash"></i> Удалить
                                                @endisset
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if($permissionToView)

                    @include('templates.take_form')

                    <p class="text-success">Найдено записей: <b>{{ $tripTicketsCountResult }}</b></p>
                @endif
            </div>
        </div>
    </div>
@endsection
