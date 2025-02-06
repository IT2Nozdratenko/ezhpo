@extends('layouts.app')

@section('title', $title)
@section('sidebar', 1)
@section('class-page', 'anketa-' . $type_ankets)

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
    </style>
@endsection

@section('custom-scripts')
    <script type="text/javascript">
        window.onload = function () {
            @if($filter_activated)
            $.get(location.href + '&getCounts=1').done(data => {
                if (data) {
                    $('#COUNTS_ANKETAS').html(`
                            <p class="text-success">Кол-во Автомобилей: <b>${data.anketasCountCars}</b></p>
                            <p class="text-success">Кол-во Водителей: <b>${data.anketasCountDrivers}</b></p>
                            <p class="text-success">Кол-во Компаний: <b>${data.anketasCountCompany}</b></p>
                        `);
                }
            })
            @endif
        };

        @if (user()->fields_visible)
        let fieldsVisible = {!! user()->fields_visible !!};
        ;
        @else
        let fieldsVisible = @json(config('fields.visible'));
        @endif

        const type = $('.ankets-form').attr('anketa');

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
            setVisibleInputs();

            const showTableData = el => {
                if (el) {
                    const id = el.attr('name');
                    const prop_checked = el.prop('checked');

                    const anketsTable = $(`.ankets-table thead th[data-field-key="${id}"], .ankets-table tbody tr td[data-field-key="${id}"]`)
                    const displayProp = !prop_checked ? 'none' : 'table-cell'

                    anketsTable.attr('hidden', !prop_checked).css({'display': displayProp})

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
                const type = el.parents('.ankets-form').attr('anketa');
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
        const SELECTED_ANKETS_ITEM = 'selectedAnkets'
        const anketsApi = {
            massTrash: '{{ route('forms.mass-trash') }}',
            massApprove: '{{ route('forms.changeMultipleResultDop') }}',
        }
        const data = {
            items: [],
            total: 0
        }

        function updateAnketsCheckbox() {
            const anketsStorage = getAnketsStorage()

            $('.hv-checkbox-mass-deletion').prop('checked', false)
            anketsStorage.items.forEach(function (item) {
                $(`.hv-checkbox-mass-deletion[data-id="${item}"]`).prop('checked', true)
            })
        }

        function getAnketsStorage() {
            if (data === null) {
                return {
                    items: [],
                    total: 0
                }
            }

            return data
        }

        function setAnketsStorage(value) {
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

        function updateAnketsControl() {
            const control = $('#selected-ankets-control')
            const controlBtnDelete = $('#selected-ankets-control-btn-delete')
            const approveBtn = $('#approve-selected')

            const anketsStorage = getAnketsStorage()

            if (anketsStorage.total) {
                const records = pronunciationWithNumber(anketsStorage.total, 'анкету', 'анкеты', 'анкет')
                const label = "Удалить " + anketsStorage.total + " " + records
                const approveLabel = "<i class=\"fa fa-check\"></i> Утвердить " + anketsStorage.total + " " + records

                controlBtnDelete.html(label)
                approveBtn.html(approveLabel)
                control.addClass('d-flex')
                control.removeClass('d-none')
            } else {
                control.addClass('d-none')
                control.removeClass('d-flex')
            }
        }

        function clearAnketsStorage() {
            data.items = []
            data.total = 0
        }

        function pushAnketaToStorage(id) {
            const anketsStorage = getAnketsStorage()

            if (anketsStorage.items.filter(item => item === id).length) {
                return
            }

            anketsStorage.total++
            anketsStorage.items.push(id)

            setAnketsStorage(anketsStorage)
        }

        function removeAnketaFromStorage(id) {
            const anketsStorage = getAnketsStorage()

            anketsStorage.items = anketsStorage.items.filter(item => item !== id)
            anketsStorage.total = anketsStorage.items.length

            setAnketsStorage(anketsStorage)
        }

        $(document).ready(function () {
            clearAnketsStorage()
            updateAnketsControl()
            updateAnketsCheckbox()

            $('.hv-checkbox-mass-deletion').click(function () {
                const id = $(this).attr('data-id')
                const checked = $(this).is(':checked')

                if (checked) {
                    pushAnketaToStorage(id)
                } else {
                    removeAnketaFromStorage(id)
                }

                updateAnketsControl()
            })

            $('#selected-ankets-control-btn-unset').click(function () {
                clearAnketsStorage()
                updateAnketsControl()
                updateAnketsCheckbox()
            })

            $('#selected-ankets-control-btn-delete').click(function () {
                const anketsStorage = getAnketsStorage()

                axios
                    .get(anketsApi.massTrash, {
                        params: {
                            action: '{{ request()->get('trash') ? 0 : 1 }}',
                            ids: anketsStorage.items
                        }
                    })
                    .then(() => {
                        clearAnketsStorage()
                        window.location.reload()
                    })
                    .catch(() => {
                    })
            })

            $('#approve-selected').click(function (e) {
                const anketsStorage = getAnketsStorage()

                axios
                    .create({
                        headers: {
                            Authorization: 'Bearer ' + API_TOKEN
                        }
                    })
                    .post(anketsApi.massApprove, {
                        ids: anketsStorage.items,
                    })
                    .then((response) => {
                        clearAnketsStorage()
                        window.location.reload()
                    })
                    .catch(error => {
                        console.log(error.response.data)
                    })
            })

            $('#select-all').click(function () {
                $('.ankets-table input[type="checkbox"]').each(function () {
                    if (!$(this).prop('checked')) {
                        $(this).click();
                    }
                });
            })

            $('.hv-btn-trash').click(function (e) {
                e.stopPropagation()
                const id = $(this).attr('data-id')

                removeAnketaFromStorage(id)
                updateAnketsControl()
            })

            $('#ankets-labeling-print-btn').click(function () {
                const anketsStorage = getAnketsStorage()

                axios({
                    method: 'post',
                    url: '{{ route('ankets.export-pdf-labeling') }}',
                    data: {
                        anket_ids: anketsStorage.items,
                    },
                    responseType: 'blob',
                })
                    .then((response) => {
                        const url = window.URL.createObjectURL(new Blob([response.data]))
                        const link = document.createElement('a')

                        link.href = url
                        link.setAttribute('download', 'Маркировка осмотров.pdf')

                        document.body.appendChild(link)

                        link.click()
                        link.remove()
                    })
                    .catch((error) => {
                        const status = error.response.status;
                        let message = 'При формировании файла произошла ошибка';

                        if (status === 422) {
                            message = 'Превышено максимально допустимое количество осмотров для печати'
                        }

                        swal.fire({
                            title: message,
                            icon: 'error'
                        });
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
    use App\Enums\FormTypeEnum;use App\Models\TripTicket;

    function checkChangeResult($anketa) {
        if (!$anketa->is_dop) {
            return false;
        }

        if ($anketa->result_dop) {
            return false;
        }

        if (!$anketa->company_id || !$anketa->company_name || !$anketa->date) {
            return false;
        }

        if ($anketa->type_anketa === FormTypeEnum::MEDIC) {
            if (!$anketa->driver_id || !$anketa->driver_fio) {
                return false;
            }
        }

        if ($anketa->type_anketa === FormTypeEnum::TECH) {
            if (!$anketa->car_id || !$anketa->car_gos_number) {
                return false;
            }
        }

        return true;
    }

    $permissionToView = (
        user()->access('medic_read') && $type_ankets == FormTypeEnum::MEDIC
        || user()->access('tech_read') && $type_ankets == FormTypeEnum::TECH
        || user()->access('journal_briefing_bdd_read') && $type_ankets == FormTypeEnum::BDD
        || user()->access('journal_pl_read') && $type_ankets == FormTypeEnum::PRINT_PL
        || user()->access('map_report_read') && $type_ankets == FormTypeEnum::REPORT_CARD
        || user()->access('errors_sdpo_read') && $type_ankets == FormTypeEnum::PAK
        || user()->access('approval_queue_view') && $type_ankets == FormTypeEnum::PAK_QUEUE
        || $type_ankets == TripTicket::SLUG
    );

    $permissionToTrashView = (
        user()->access('medic_trash') && $type_ankets == FormTypeEnum::MEDIC
        || user()->access('tech_trash') && $type_ankets == FormTypeEnum::TECH
        || user()->access('journal_briefing_bdd_trash') && $type_ankets == FormTypeEnum::BDD
        || user()->access('journal_pl_trash') && $type_ankets == FormTypeEnum::PRINT_PL
        || user()->access('map_report_trash') && $type_ankets == FormTypeEnum::REPORT_CARD
        || user()->access('errors_sdpo_trash') && $type_ankets == FormTypeEnum::PAK
        || $type_ankets == TripTicket::SLUG
    );

    $duplicateView = (
        $type_ankets === FormTypeEnum::MEDIC && user()->hasRole('medic')
        || $type_ankets === FormTypeEnum::TECH && user()->hasRole('tech')
        || user()->hasRole('admin') && in_array($type_ankets, [FormTypeEnum::MEDIC, FormTypeEnum::TECH])
    );

    $permissionToDelete = (
        $type_ankets == FormTypeEnum::MEDIC && user()->access('medic_delete')
        || $type_ankets == FormTypeEnum::TECH && user()->access('tech_delete')
        || $type_ankets == FormTypeEnum::BDD && user()->access('journal_briefing_bdd_delete')
        || user()->access('journal_pl_delete') && $type_ankets == FormTypeEnum::PRINT_PL
        || user()->access('map_report_delete') && $type_ankets == FormTypeEnum::REPORT_CARD
        || user()->access('errors_sdpo_delete') && $type_ankets == FormTypeEnum::PAK
        || $type_ankets == TripTicket::SLUG
    );

    $permissionToUpdate = (
        $type_ankets == FormTypeEnum::MEDIC && user()->access('medic_update')
        || $type_ankets == FormTypeEnum::TECH && user()->access('tech_update')
        || $type_ankets == FormTypeEnum::BDD && user()->access('journal_briefing_bdd_update')
        || user()->access('journal_pl_update') && $type_ankets == FormTypeEnum::PRINT_PL
        || user()->access('map_report_update') && $type_ankets == FormTypeEnum::REPORT_CARD
        || user()->access('errors_sdpo_update') && $type_ankets == FormTypeEnum::PAK
        || $type_ankets == TripTicket::SLUG

    );

    $permissionToExport = (
        $type_ankets == FormTypeEnum::TECH && user()->access('tech_export')
        || $type_ankets == FormTypeEnum::MEDIC && user()->access('medic_export')
        || $type_ankets == FormTypeEnum::BDD && user()->access('journal_briefing_bdd_export')
        || $type_ankets == FormTypeEnum::PRINT_PL && user()->access('journal_pl_export')
        || $type_ankets == FormTypeEnum::REPORT_CARD && user()->access('map_report_export')
        || $type_ankets == TripTicket::SLUG
    );

    $permissionToExportPrikaz = (
        $type_ankets == FormTypeEnum::TECH && user()->access('tech_export_prikaz')
        || $type_ankets == FormTypeEnum::MEDIC && user()->access('medic_export_prikaz')
        || $type_ankets == FormTypeEnum::BDD && user()->access('journal_briefing_bdd_export_prikaz')
        || $type_ankets == FormTypeEnum::PRINT_PL && user()->access('journal_pl_export_prikaz')
        || $type_ankets == FormTypeEnum::REPORT_CARD && user()->access('map_report_export_prikaz')
        || $type_ankets == TripTicket::SLUG

    );

    $permissionToExportPrikazPL = (
        $type_ankets == FormTypeEnum::TECH && user()->access('tech_export_prikaz_pl')
    );

    $permissionToPrintAnketLabeling = (
        user()->access('medic_read') && $type_ankets == 'medic'
        || user()->access('tech_read') && $type_ankets == 'tech'
    );

    $notDeletedItems = session('not_deleted_ankets');
    $approveErrors = session('mass_approve_errors');
@endphp

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div>
                    @if($type_ankets !== FormTypeEnum::PAK_QUEUE)
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
                                            <a href="{{ route('home', $type_ankets) }}" class="btn btn-sm btn-warning">Назад</a>
                                        @else
                                            <a href="?trash=1" class="btn btn-sm btn-warning">Корзина <i
                                                    class="fa fa-trash"></i></a>
                                        @endisset
                                    @endif
                                    @if($duplicateView)
                                        @if(request()->filled('duplicates'))
                                            <a href="{{ route('home', $type_ankets) }}"
                                               class="btn btn-sm btn-secondary">Назад</a>
                                        @else
                                            <a href="?duplicates=true" class="btn btn-sm btn-secondary">Показать
                                                дубликаты <i class="fa fa-clone"></i></a>
                                        @endif
                                    @endif
                                </div>
                                @if($type_ankets === FormTypeEnum::TECH)
                                    <div class="col-md-6 text-right">
                                        @if($permissionToExport)
                                            <a href="?export=1&{{ $queryString }}" class="btn btn-sm btn-default">Экспорт
                                                таблицы <i class="fa fa-download"></i></a>
                                        @endif
                                        @if($permissionToExportPrikaz)
                                            <a href="?export=1&{{ $queryString }}&exportPrikaz=1"
                                               class="btn btn-sm btn-default">Экспорт таблицы по приказу ТО <i
                                                    class="fa fa-download"></i></a>
                                        @endif
                                        @if($permissionToExportPrikazPL)
                                            <a href="?export=1&{{ $queryString }}&exportPrikazPL=1"
                                               class="btn btn-sm btn-default">Экспорт таблицы по приказу ПЛ <i
                                                    class="fa fa-download"></i></a>
                                        @endif
                                    </div>
                                @else
                                    <div class="col-md-6 text-right">
                                        @if($permissionToExport)
                                            <a href="?export=1&{{ $queryString }}" class="btn btn-sm btn-default">Экспорт
                                                таблицы <i class="fa fa-download"></i></a>
                                        @endif
                                        @if($permissionToExportPrikaz)
                                            <a href="?export=1&{{ $queryString }}&exportPrikaz=1"
                                               class="btn btn-sm btn-default">Экспорт таблицы по приказу <i
                                                    class="fa fa-download"></i></a>
                                        @endif
                                    </div>
                                @endif

                                @if (!user()->hasRole('client'))
                                    <div class="toggle-hidden p-3" id="ankets-filters">
                                        <form class="ankets-form" anketa="{{ $type_ankets }}">
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

                        @if($permissionToView)
                            <ul
                                class="nav nav-tabs"
                                id="filter-groups"
                                role="tablist">
                                <li class="nav-item">
                                    <a
                                        class="nav-link active"
                                        id="filter-group-1-tab"
                                        data-toggle="tab"
                                        href="#filter-group-1"
                                        role="tab"
                                        aria-controls="filter-group-1"
                                        aria-selected="true">
                                        <i class="fa fa-filter"></i>Первая группа фильтров
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a
                                        class="nav-link"
                                        id="filter-group-2-tab"
                                        data-toggle="tab"
                                        href="#filter-group-2"
                                        role="tab"
                                        aria-controls="filter-group-2"
                                        aria-selected="false">
                                        <i class="fa fa-filter"></i>Вторая группа фильтров
                                    </a>
                                </li>
                            </ul>

                            <form onsubmit="document.querySelector('#page-preloader').classList.remove('hide')"
                                  action="" method="GET" class="tab-content ankets-form-filter mb-3 pt-3"
                                  id="filter-groupsContent">
                                <div class="text-center">
                                    <img src="{{ asset('images/loader.gif') }}" width="30" class="mb-4"/>
                                </div>
                            </form>
                        @endif
                    @elseif(user()->access('approval_queue_clear'))
                        <a href="?clear=1&type_anketa={{ $type_ankets }}" class="btn btn-warning mb-2">Очистить
                            очередь</a>
                    @endif

                    @if(session()->has('error'))
                        <div class="alert alert-danger" role="alert">{{ session()->get('error') }}</div>
                    @endif

                    @if(count($ankets) > 0 && $permissionToView)
                        <div id="selected-ankets-control" class="d-none align-items-center mt-4 mb-2">
                            @if($permissionToDelete)
                                <button id="selected-ankets-control-btn-delete"
                                        class="btn btn-danger btn-sm mr-2"></button>
                            @endif
                            <button id="approve-selected" class="btn btn-success btn-sm"></button>
                            <button id="select-all" class="btn btn-success btn-sm ml-2">Выделить все на странице
                            </button>
                            <button id="selected-ankets-control-btn-unset" class="btn btn-success btn-sm ml-2">Снять
                                выделение
                            </button>

                            @if($permissionToPrintAnketLabeling)
                                <button id="ankets-labeling-print-btn" class="btn btn-success btn-sm ml-2">Печать
                                    маркировки
                                </button>
                            @endif
                        </div>
                    @endif

                    @if($notDeletedItems)
                        <div id="hv-alert-error"
                             class="alert alert-danger hv-mass-deletion-alert-error d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <div>Не удалось удалить анкеты с ID:</div>
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

                    @if($approveErrors)
                        <div id="hv-alert-error"
                             class="alert alert-danger hv-mass-deletion-alert-error d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <div>Во время утверждения возникли ошибки:</div>
                                <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                                    @foreach($approveErrors as $item)
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

                    <hr>

                    @if(count($ankets) > 0)
                        {{ $ankets->appends($_GET)->render() }}
                    @endif
                </div>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-body">
                @if((count($ankets) > 0) && $permissionToView)
                    <table
                        id="ankets-table"
                        class="ankets-table table table-striped table-sm">
                        <thead>
                        <tr>
                            <th>#</th>

                            @if($type_ankets === 'pak_queue')
                                <th class="not-export">Таймер</th>
                            @endif

                            @foreach($fieldPrompts as $field)
                                <th
                                    data-field-key="{{ $field->field }}"
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

                            <th class="not-export">
                                @if($permissionToUpdate && ($type_ankets === FormTypeEnum::MEDIC))
                                    <a class="not-export"
                                       href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey=result_dop&{{ $queryString }}">
                                        <i class="fa fa-sort"></i>
                                    </a>
                                @endif
                                #
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($ankets as $anketaKey => $anketa)
                            <tr data-field="{{ $anketaKey }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        data-id="{{ $anketa->id }}"
                                        class="hv-checkbox-mass-deletion">
                                </td>

                                @if($type_ankets === FormTypeEnum::PAK_QUEUE)
                                    <td class="not-export">
                                        <div
                                            class="App-Timer"
                                            data-date="{{ $anketa->created_at }}">
                                        </div>
                                    </td>
                                @endif

                                @foreach($fieldPrompts as $field)
                                    <td data-field-key="{{ $field->field }}"
                                        @isset($blockedToExportFields[$field->field])
                                            class="not-export"
                                        @endisset>
                                        @if(($field->field === 'date' || strpos($field->field, '_at') > 0) && $anketa[$field->field])
                                            @if ($field->field === 'date' && $type_ankets === FormTypeEnum::BDD)
                                                {{ date('d-m-Y', strtotime($anketa[$field->field])) }}
                                            @else
                                                {{ date('d-m-Y H:i:s', strtotime($anketa[$field->field])) }}
                                            @endif
                                        @elseif(($field->field === 'photos') && $anketa[$field->field])
                                            @php $photos = explode(',', $anketa[$field->field]) @endphp
                                            @foreach($photos as $phI => $ph)
                                                @php $isUri = strpos($ph, 'sdpo.ta-7'); @endphp

                                                @if($phI == 0)
                                                    <a href="{{ $isUri ? $ph : Storage::url($ph) }}"
                                                       data-fancybox="gallery_{{ $anketa->id }}">
                                                        <i class="fa fa-camera"></i>({{ count($photos) }})
                                                    </a>
                                                @else
                                                    <a href="{{ $isUri ? $ph : Storage::url($ph) }}"
                                                       data-fancybox="gallery_{{ $anketa->id }}">
                                                    </a>
                                                @endif
                                            @endforeach
                                        @elseif(($field->field === 'videos') && $anketa[$field->field])
                                            @php $videos = explode(',', $anketa[$field->field]) @endphp
                                            @foreach($videos as $vK => $vV)
                                                @if($vK == 0)
                                                    <a
                                                        data-type="iframe"
                                                        href="{{ route('showVideo', ['url' => $vV]) }}"
                                                        data-fancybox="video_{{ $anketa->id }}">
                                                        <i class="fa fa-video-camera"></i>
                                                        ({{ count($videos) }})
                                                    </a>
                                                @else
                                                    <a data-type="iframe" href="{{ $vV }}"
                                                       data-fancybox="video_{{ $anketa->id }}"></a>
                                                @endif
                                            @endforeach
                                        @elseif($field->field === 'company_name' && user()->access('company_read'))
                                            <a href="{{ route('renderElements', ['model' => 'Company', 'filter' => 1, 'name' => $anketa[$field->field] ]) }}">
                                                {{ $anketa[$field->field] }}
                                            </a>
                                        @elseif($field->field === 'user_name' && user()->access('employee_read'))
                                            <a href="{{ route('users', ['name' => $anketa[$field->field] ]) }}">
                                                {{ $anketa[$field->field] }}
                                            </a>
                                        @elseif($field->field === 'operator_id' && user()->access('employee_read'))
                                            @if($anketa->operator_id)
                                                <a href="{{ route('users', ['id' => $anketa[$field->field] ]) }}">
                                                    {{ $anketa->operator ? $anketa->operator->name : 'Отсутствует' }}
                                                </a>
                                            @else
                                                Отсутствует
                                            @endif
                                        @elseif($field->field === 'driver_fio' && user()->access('drivers_read'))
                                            <a href="{{ route('renderElements', ['model' => 'Driver', 'filter' => 1, 'fio' => $anketa[$field->field] ]) }}">
                                                {{ $anketa[$field->field] }}
                                            </a>
                                        @elseif($field->field === 'car_gos_number' && user()->access('cars_read'))
                                            <a href="{{ route('renderElements', ['model' => 'Car', 'filter' => 1, 'gos_number' => $anketa[$field->field] ]) }}">
                                                {{ $anketa[$field->field] }}
                                            </a>
                                        @elseif($field->field === 'protokol_path')
                                            @if ($anketa->proba_alko === 'Положительно' && user()->access('medic_protokol_view'))
                                                @if(user()->access('medic_protokol_edit'))
                                                    <a class="btn primary btn-sm btn-table"
                                                       href="{{ route('docs.get', ['type' => 'protokol', 'anketa_id' => $anketa->id]) }}">
                                                        Протокол отстранения
                                                    </a>
                                                @else
                                                    <a class="btn primary btn-sm btn-table"
                                                       target="_blank"
                                                       href="{{ route('docs.get.pdf', ['type' => 'protokol', 'anketa_id' => $anketa->id]) }}">
                                                        Протокол отстранения
                                                    </a>
                                                @endif
                                            @else
                                                Отсутствует
                                            @endif
                                        @else

                                            {{ $anketa[$field->field] }}

                                            @if($type_ankets === FormTypeEnum::MEDIC && $field->field === 'admitted' && $anketa[$field->field] === 'Не допущен' && user()->access('medic_closing_view'))
                                                @if(user()->access('medic_closing_edit'))
                                                    <div class="row d-flex"
                                                         style="gap: 3px">
                                                        <a class="btn primary btn-sm btn-table"
                                                           href="{{ route('docs.get', ['type' => 'closing', 'anketa_id' => $anketa->id]) }}">
                                                            Мед. заключение
                                                        </a>

                                                        @if($anketa['closing_path'])
                                                            <a target="_blank"
                                                               class="btn primary btn-sm btn-table"
                                                               href="{{ route('docs.get.pdf', ['type' => 'closing', 'anketa_id' => $anketa->id]) }}">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="row d-flex"
                                                         style="gap: 3px">
                                                        <a target="_blank"
                                                           class="btn primary btn-sm btn-table"
                                                           href="{{ route('docs.get.pdf', ['type' => 'closing', 'anketa_id' => $anketa->id]) }}">
                                                            Мед. заключение
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                @endforeach


                                @if($permissionToDelete && request()->get('trash'))
                                    <td class="td-option">
                                        {{ ($anketa->deleted_user_name) }}
                                    </td>
                                    <td class="td-option">
                                        {{ ($anketa->deleted_at) }}
                                    </td>
                                @endif

                                <td class="td-option not-export d-flex justify-content-end">
                                    @if($type_ankets === FormTypeEnum::PAK_QUEUE)
                                        <a href="{{ route('forms.get', $anketa->id) }}"
                                           class="btn btn-sm btn-info mr-1"><i class="fa fa-search mr-1"></i></a>
                                        <a href="{{ route('forms.changePakQueue', ['admitted' => 'Допущен', 'id' => $anketa->id]) }}"
                                           class="btn btn-sm btn-success mr-1"><i class="fa fa-check"></i></a>
                                        <a href="{{ route('forms.changePakQueue', ['admitted' => 'Не идентифицирован', 'id' => $anketa->id]) }}"
                                           class="btn btn-sm btn-secondary mr-1"><i class="fa fa-question"></i></a>
                                        <a href="{{ route('forms.changePakQueue', ['admitted' => 'Не допущен', 'id' => $anketa->id]) }}"
                                           class="btn btn-sm btn-danger mr-1"><i class="fa fa-close"></i></a>
                                    @endif

                                    @if($anketa->is_dop && ! checkChangeResult($anketa))
                                        <button class="btn btn-sm btn-outline-success mr-1"
                                                title="{{ $anketa->result_dop }}"
                                                style="cursor: default" disabled>
                                            <i class="fa fa-check"></i>
                                        </button>
                                    @endif

                                    @if($permissionToUpdate)
                                        @if($anketa->is_dop && !$anketa->result_dop && checkChangeResult($anketa))
                                            <a href="{{ route('forms.changeResultDop', ['result_dop' => 'Утвержден', 'id' => $anketa->id]) }}"
                                               class="btn btn-sm btn-success mr-1">
                                                <i class="fa fa-check"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('forms.get', $anketa->id) }}"
                                           class="btn btn-info btn-sm mr-1"><i class="fa fa-edit"></i></a>
                                    @endif

                                    @if($type_ankets === FormTypeEnum::MEDIC && mb_strtolower($anketa->admitted ?? '') === 'допущен')
                                        <a
                                            href="{{ route('forms.print', ['id' => $anketa->id]) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-success mr-1">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    @endif

                                    @if(($type_ankets !== FormTypeEnum::PAK_QUEUE) && $permissionToDelete)
                                        @include('pages.home.components.buttons.delete-form-btn', compact('anketa'))
                                    @endif
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

                    <p class="text-success">Найдено записей: <b>{{ $anketasCountResult }}</b></p>
                @endif

                <div id="COUNTS_ANKETAS">

                </div>
            </div>
        </div>
    </div>
@endsection
