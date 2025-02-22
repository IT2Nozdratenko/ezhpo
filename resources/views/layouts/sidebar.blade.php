@php
    use App\Enums\FormTypeEnum;
    use Illuminate\Support\Facades\Cache;
    use App\Models\Forms\Form;
    use App\Services\Terminals\TerminalsToCheckService;
    use App\Actions\Terminals\GetTerminalsToCheck\GetTerminalsToCheckQuery;

    /** @var \App\User $user */
    $user = \Illuminate\Support\Facades\Auth::user();
    $accessToJournals = $user->access(
            'medic_read',
            'tech_read',
            'journal_briefing_bdd_read',
            'journal_pl_read',
            'map_report_read',
            'errors_sdpo_read',
            'errors_sdpo_create',
            'trip_tickets_read'
        );

    $accessToSettings = $user->access(
            'system_read',
            'settings_system_read',
            'city_read',
            'pv_read',
            'employee_read',
            'employee_create',
            'group_create',
            'city_create',
            'group_read',
            'story_field_read',
            'story_field_create',
            'pv_create',
            'date_control_create',
            'date_control_read',
            'pak_sdpo_read',
            'pak_sdpo_create',
            'pak_sdpo_update',
            'requisites_read',
            'requisites_create',
            'releases_read'
        );

    $accessToElements = $user->access(
            'drivers_read',
            'cars_read',
            'company_read',
            'service_read',
            'discount_read',
            'briefings_read',
            'drivers_create',
            'cars_create',
            'company_create',
            'service_create',
            'discount_create',
            'briefings_create',
            'contract_read',
            'contract_create'
        );
@endphp

    <!-- Side Navbar -->
<nav class="side-navbar">
    <!-- Sidebar Header-->
    <div class="sidebar-header d-flex align-items-center">
        <div class="title">
            <article>
                @foreach($user->roles as $role)
                    <h3>
                        <span class="badge badge-success text-wrap">
                            {{ $role->guard_name }}
                        </span>
                    </h3>
                @endforeach
            </article>
        </div>
    </div>

    <ul class="list-unstyled">
        <li>
            <a class="bg-info text-white">МЕНЮ</a>
        </li>

        @if($user->access('medic_create'))
            <li>
                <a href="{{ route('forms.index', ['type' => FormTypeEnum::MEDIC]) }}"
                   class="bg-red text-white"><i
                        class="icon-padnote"></i>Провести мед. осмотр</a>
            </li>
        @endif

        @if($user->access('tech_create'))
            <li>
                <a href="{{ route('forms.index', ['type' => FormTypeEnum::TECH]) }}"
                   class="bg-blue text-white"><i
                        class="icon-padnote"></i>Провести тех. осмотр</a>
            </li>
        @endif

        @if($user->access('map_report_create'))
            <li>
                <a href="{{ route('forms.index', ['type' => FormTypeEnum::REPORT_CARD]) }}"
                   class="bg-gray"><i
                        class="icon-padnote"></i>Внести Отчёт с карты</a>
            </li>
        @endif

        @if($user->access('print_register_pl_create'))
            <li>
                <a href="{{ route('forms.index', ['type' => FormTypeEnum::PRINT_PL]) }}" class="bg-gray"><i
                        class="icon-padnote"></i>Внести запись в Реестр печати ПЛ</a>
            </li>
        @endif

        @if($user->access('trip_tickets_create'))
            <li>
                <a href="{{ route('trip-tickets.create') }}" class="bg-gray"><i
                        class="icon-padnote"></i>Внести путевой лист</a>
            </li>
        @endif

        @if($user->access('journal_briefing_bdd_create'))
            <li>
                <a href="{{ route('forms.index', ['type' => FormTypeEnum::BDD]) }}" class="bg-gray"><i
                        class="icon-padnote"></i>Внести Инструктаж БДД</a>
            </li>
        @endif

        @if($user->access('approval_queue_view', 'approval_queue_clear'))
            @php
                $countPakQueue = Form::pakQueueCount($user);
            @endphp
            <li>
                <a href="{{ route('pak.index') }}">
                    <i class="fa fa-users"></i>Очередь утверждения
                    <span class="badge bg-primary text-white">
                        {{ $countPakQueue < 99 ? $countPakQueue : '99+' }}
                    </span>
                </a>
            </li>
        @endif


        @if($user->access('client_create'))
            <li>
                <a href="{{ route('pages.add_client') }}" class="bg-info text-white"><i class="icon-user"></i>Добавить
                    клиента</a>
            </li>
        @endif

        @if($accessToJournals)
            <li>
                <a href="#" data-btn-collapse="#views" role="button"> <i class="icon-grid"></i>Журналы осмотров</a>
                <ul id="views" class="collapse list-unstyle">
                    @if($user->access('medic_read'))
                        <li><a href="{{ route('home', FormTypeEnum::MEDIC) }}"><i class="fa fa-plus"></i>Журнал
                                МО</a></li>
                    @endif

                    @if($user->access('tech_read'))
                        <li><a href="{{ route('home', FormTypeEnum::TECH) }}"><i class="fa fa-wrench"></i>Журнал
                                ТО</a></li>
                    @endif

                    @if($user->access('journal_briefing_bdd_read'))
                        <li>
                            <a href="{{ route('home', FormTypeEnum::BDD) }}">
                                <i class="fa fa-book"></i>Журнал инструктажей по БДД
                            </a>
                        </li>
                    @endif

                    @if($user->access('trip_tickets_read'))
                        <li>
                            <a href="{{ route('trip-tickets.index') }}"><i class="fa fa-book"></i>Реестр путевых листов</a>
                        </li>
                    @endif

                    @if($user->access('journal_pl_read'))
                        <li>
                            <a href="{{ route('home', FormTypeEnum::PRINT_PL) }}"><i class="fa fa-book"></i>Журнал
                                печати
                                ПЛ</a>
                        </li>
                    @endif

                    @if($user->access('map_report_read'))
                        <li>
                            <a href="{{ route('home', FormTypeEnum::REPORT_CARD) }}"><i
                                    class="fa fa-book"></i>Реестр снятия
                                отчетов
                                с карт</a>
                        </li>
                    @endif


                    @if($user->access('errors_sdpo_read', 'errors_sdpo_create'))
                        @php
                            $countErrorsPak = Cache::remember('countErrorsPak', 3600,
                                function () {
                                    return Form::where('type_anketa', FormTypeEnum::PAK)->count();
                                }
                            );

                        @endphp
                        <li>
                            <a href="{{ route('home', 'pak') }}"><i class="fa fa-close"></i>Реестр ошибок СДПО <span
                                    class="badge bg-primary text-white">{{ $countErrorsPak < 99 ? $countErrorsPak : '99+' }}
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if($user->access('report_service_company_read', 'report_schedule_pv_read'))
            <li>
                <a href="#" data-btn-collapse="#reports" role="button"><i class="fa fa-area-chart"></i> Отчеты</a>
                <ul id="reports" class="collapse list-unstyle">
                    @if($user->access('report_schedule_pv_read'))
                        <li>
                            <a href="{{ route('report.get', 'graph_pv') }}">
                                <i class="fa fa-book"></i>График работы пунктов выпуска
                            </a>
                        </li>
                    @endif

                    @if($user->access('report_service_company_read', 'report_service_company_export'))
                        <li>
                            <a href="{{ route('report.journal') }}">
                                <i class="fa fa-book"></i>Отчет по услугам компании
                            </a>
                        </li>
                    @endif

                    @if($user->access('report_schedule_dynamic_mo'))
                        <li>
                            <a href="{{ route('report.dynamic', ['journal' => FormTypeEnum::MEDIC]) }}">
                                <i class="fa fa-book"></i>Отчет по количеству осмотров
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if($accessToElements)
            <li>
                <a href="#" data-btn-collapse="#phoenic" role="button"> <i
                        class="icon-interface-windows"></i>CRM</a>
                <ul id="phoenic" class="collapse list-unstyle">

                    @if($user->access('contract_read', 'contract_create'))
                        <li>
                            <a href="/contract">
                                Договор
                                <span class="
                                    start-100
                                    translate-middle
                                    badge
                                    text-white
                                    rounded-pill
                                    bg-success"
                                >
                                    new
                                </span>
                            </a>
                        </li>
                    @endif
                    @if($user->access('drivers_read', 'drivers_create'))
                        <li><a href="{{ route('renderElements', 'Driver') }}">Водители</a></li>
                    @endif
                    @if($user->access('cars_read', 'cars_create'))
                        <li><a href="{{ route('renderElements', 'Car') }}">Автомобили</a></li>
                    @endif
                    @if($user->access('company_read', 'company_create'))
                        <li><a href="{{ route('renderElements', 'Company') }}">Компании</a></li>
                    @endif
                    @if($user->access('service_read', 'service_create'))
                        <li><a href="{{ route('renderElements', 'Product') }}">Услуги</a></li>
                    @endif
                    @if($user->access('discount_read', 'discount_create'))
                        <li><a href="{{ route('renderElements', 'Discount') }}">Скидки</a></li>
                    @endif
                    @if($user->access('briefings_read', 'briefings_create'))
                        <li><a href="{{ route('renderElements', 'Instr') }}">Виды инструктажей</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($accessToSettings)
            <li>
                <a href="#" data-btn-collapse="#spis-pol" role="button"><i class="fa fa-cog"></i> Настройки</a>
                <ul id="spis-pol" class="collapse list-unstyle">

                    @if($user->access('settings_system_read'))
                        <li><a href="{{ route('settings.index') }}">Системные настройки</a></li>
                    @endif

                    @if($user->access('city_read', 'city_create'))
                        <li><a href="{{ route('renderElements', 'Town') }}">Города</a></li>
                    @endif

                    @if($user->access('pv_read', 'pv_create'))
                        <li><a href="{{ route('renderElements', 'Point') }}">Пункты выпуска</a></li>
                    @endif

                    @if($user->access('employee_read', 'employee_create'))
                        <li><a href="{{ route('users') }}">Сотрудники</a></li>
                    @endif

                    @if($user->access('group_read', 'group_create'))
                        <li><a href="{{ route('roles.index') }}"> Роли </a></li>
                    @endif

                    @if($user->access('pak_sdpo_read', 'pak_sdpo_create'))
                        @php
                            $service = new TerminalsToCheckService();
                            $needToCheck = $service->getIds();

                            $lessMonthCount = count($needToCheck['less_month']);
                            $expiredCount = count($needToCheck['expired']);

//                            $query = new GetTerminalsToCheckQuery();
//                            $terminalsToCheckViewModel = $query->get();
//
//                            $lessMonthCount = count($terminalsToCheckViewModel->getLessMonth());
//                            $expiredCount = count($terminalsToCheckViewModel->getExpired());
                        @endphp
                        <li>
                            <a href="{{ route('terminals.index') }}">
                                Терминалы
                                @if($lessMonthCount)
                                    <span
                                        class="badge bg-warning text-white">{{ $lessMonthCount < 99 ? $lessMonthCount : '99+' }}</span>
                                @endif
                                @if($expiredCount)
                                    <span
                                        class="badge bg-primary text-white">{{ $expiredCount < 99 ? $expiredCount : '99+' }}</span>
                                @endif
                            </a>
                        </li>
                    @endif

                    @if($user->access('stamp_read'))
                        <li><a href="{{ route('stamp.index') }}">Штампы</a></li>
                    @endif

                    @if($user->access('date_control_read', 'date_control_create'))
                        <li><a href="{{ route('renderElements', 'DDates') }}">Контроль дат</a></li>
                    @endif

                    @if($user->access('requisites_read', 'requisites_create'))
                        <li><a href="{{ route('renderElements', 'Req') }}">Реквизиты нашей компании</a></li>
                    @endif

                    @if($user->access('field_prompt_read'))
                        <li><a href="{{ route('prompt.index') }}">Подсказки полей</a></li>
                    @endif

                    @if($user->access('logs_read'))
                        <li><a href="{{ route('logs.index') }}">Журнал действий</a></li>
                        <li><a href="{{ route('form-logs.index') }}">Журнал действий с осмотрами</a></li>
                    @endif

                    @if($user->access('sdpo_crash_logs_read'))
                        <li><a href="{{ route('sdpo_crash_logs.index') }}">Отказы СДПО</a></li>
                    @endif
                </ul>
            </li>
        @endif

    </ul>
</nav>
