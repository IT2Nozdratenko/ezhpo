@php
    use App\Enums\FormLabelingType;
    /** @var $details \App\ViewModels\FormVerificationDetails\FormVerificationDetails */

    if ($details->isVerified()) {
        $title = 'Путевой лист действителен';
    }
    else {
        $title = 'Путевой лист не найден';
    }

    $permissionToDelete = user() && (
        $details->getFormType()->value() == FormLabelingType::MEDIC && user()->access('medic_delete') ||
        $details->getFormType()->value() == FormLabelingType::TECH && user()->access('tech_delete')
    );
@endphp

@extends('pages.form-verification.base')

@section('title', $title)

@push('custom_styles')
    <style>
        .page-content {
            height: calc(100vh - 50px);
        }

        .status-icon {
            font-size: 70px;
        }

        .status-icon-success {
            color: #2fa360;
        }

        .status-icon-wrong {
            color: #a70912;
        }

        .status-title {
            margin-top: 10px;
            font-size: 18px;
        }

        .status-phone {
            display: inline-block;
            text-align: center;
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 23px;
            color: inherit !important;
        }

        .verified-item {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .verified-item + .verified-item {
            margin-top: 5px;
        }

        .verification-history-list p {
            font-size: 14px;
            font-weight: 400;
        }
    </style>
@endpush

@section('content')
    <main class="page-content d-flex align-items-stretch">
        <div class="container text-center">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-12">
                    <div class="flex justify-content-center align-items-center">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div>
                            @if($details->isVerified())
                                <div>
                                    <i class="fa fa-check-circle status-icon status-icon-success"
                                       aria-hidden="true"></i>
                                </div>
                                <div class="status-title">Путевой лист действителен</div>
                            @else
                                <div>
                                    <i class="fa fa-times-circle status-icon status-icon-wrong" aria-hidden="true"></i>
                                </div>
                                <div class="status-title">Путевой лист не найден</div>
                                <a id="phone" href="" class="d-none status-phone"></a>
                            @endif

                            <div id="verification-alert-body" class="d-none alert alert-danger mt-2">
                                <b>Вы уже проверяли данный осмотр <span id="verification-alert-count"></span></b><br>
                                <div>Дата последней проверки: <span id="verification-alert-date"></span></div>
                            </div>

                            @if($details->isVerified())
                                <div class="mt-2 d-flex flex-column align-items-center">
                                    @if($details->getFormNumber())
                                        <div class="verified-item">
                                            <b>Номер осмотра:</b>
                                            <span>{{ $details->getFormNumber() }}</span>
                                        </div>
                                    @endif

                                    <div class="verified-item">
                                        <b>Пройден:</b>
                                        @if($details->getFormDate())
                                            <span>{{ $details->getFormDate()->format('d.m.Y') }}</span>
                                        @elseif($details->getFormattedFormPeriod())
                                            <span>{{ $details->getFormattedFormPeriod() }}</span>
                                        @endif
                                    </div>

                                    @if($details->getCompanyName())
                                        <div class="verified-item">
                                            <b>Наименование компании:</b>
                                            <span>{{ $details->getCompanyName() }}</span>
                                        </div>
                                    @endif

                                    @if($details->getDriverName())
                                        <div class="verified-item">
                                            <b>ФИО водителя:</b>
                                            <span>{{ $details->getDriverName() }}</span>
                                        </div>
                                    @endif

                                    @if($details->getCarGosNumber())
                                        <div class="verified-item">
                                            <b>Гос. номер автомобиля:</b>
                                            <span>{{ $details->getCarGosNumber() }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-2">
                                    @auth
                                        @if($permissionToDelete)
                                            <a
                                                id="form-verification-delete-link"
                                                href="{{ route('forms.trash', ['id' => $details->getFormId(), 'action' => 1]) }}"
                                                class="btn btn-warning btn-sm hv-btn-trash mr-1"
                                                data-id="{{ $details->getFormId() }}">
                                                Удалить осмотр <i class="fa fa-trash ml-1"></i>
                                            </a>
                                        @endif
                                    @endauth
                                    @guest
                                        @if($details->getTripTicketDetails())
                                            <a class="btn btn-success btn-sm ml-2" type="button" href="{{ route('trip-tickets.attach-photos-page', ['id' => $details->getTripTicketDetails()->getTripTicket()->uuid]) }}">
                                                Загрузить фото ПЛ <i class="fa fa-photo ml-1"></i>
                                            </a>
                                        @endif
                                    @endguest
                                    @auth
                                        @if($details->getTripTicketDetails())
                                            <div class="mt-2" id="attach-photos">
                                                <form method="POST"
                                                      action="{{ route('trip-tickets.attach-photos', ['id' => $details->getTripTicketDetails()->getTripTicket()->uuid]) }}"
                                                      class="form-horizontal"
                                                      onsubmit="document.querySelector('#page-preloader').classList.remove('hide')"
                                                      enctype="multipart/form-data">
                                                    @csrf

                                                    <attach-photos-index
                                                        :id="'{{ $details->getTripTicketDetails()->getTripTicket()->uuid }}'"
                                                        :items="JSON.parse('{{ json_encode($details->getTripTicketDetails()->getPhotos()) }}')"
                                                    ></attach-photos-index>
                                                </form>
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <div id="history-widget" class="justify-content-center d-none">
        <a class="text-info" data-toggle="collapse" href="#collapseVerificationHistory" role="button"
           aria-expanded="false" aria-controls="collapseVerificationHistory">
            История проверок осмотра
        </a>
    </div>
    <div>
        <div class="collapse p-2" id="collapseVerificationHistory">
            <div class="alert alert-info verification-history-list">
                <div class="d-flex justify-content-center mb-2">
                    <b>Проверок всего: <span id="history-count"></span></b>
                </div>
                <div id="history-items"></div>
            </div>
        </div>
    </div>

@endsection

@push('custom_scripts')
    <script>
        const SS_KEY_SIGN = 'anketLabelingVerification_Sign';
        const SS_KEY_SESSION_KEY = 'anketLabelingVerification_SessionKey';
        const LS_KEY_ITEMS = 'anketLabelingVerification_Items';
        const LS_KEY_CLIENT_HASH = 'anketLabelingVerification_ClientHash';

        const ui = {
            verificationAlertBody: $('#verification-alert-body'),
            verificationAlertCount: $('#verification-alert-count'),
            verificationAlertDate: $('#verification-alert-date'),
            historyWidget: $('#history-widget'),
            historyCount: $('#history-count'),
            historyItems: $('#history-items'),
        };

        const currentDate = new Date();
        const currentUuid = '{{ $details->getFormUuid() }}';

        function getVerificationItems() {
            const items = localStorage.getItem(LS_KEY_ITEMS);

            if (!items) {
                localStorage.setItem(LS_KEY_ITEMS, JSON.stringify({}))
                return {};
            }

            return JSON.parse(items);
        }

        function getVisitedSign() {
            const rawData = sessionStorage.getItem(SS_KEY_SIGN)

            if (rawData) {
                const data = JSON.parse(rawData)

                return data.hasOwnProperty('visited') && data.visited
            }

            return false
        }

        function getSessionKey() {
            let sessionKey = sessionStorage.getItem(SS_KEY_SESSION_KEY)

            if (!sessionKey) {
                sessionKey = (new Date()).toISOString()
                sessionStorage.setItem(SS_KEY_SESSION_KEY, sessionKey)
            }

            return sessionKey
        }

        function formatDate(isoString) {
            const date = new Date(isoString);

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы начинаются с 0
            const year = date.getFullYear();

            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');

            return `${day}.${month}.${year} ${hours}:${minutes}:${seconds}`;
        }

        function formatCount(count) {
            const value = Math.abs(count) % 100;
            const num = value % 10;

            if (value > 10 && value < 20) return `${count} раз`;
            if (num > 1 && num < 5) return `${count} раза`;
            if (num === 1) return `${count} раз`;

            return `${count} раз`;
        }

        function checkVerified() {
            const sessionKey = getSessionKey()
            const allItems = getVerificationItems();

            if (allItems.hasOwnProperty(currentUuid)) {
                const items = allItems[currentUuid].filter(function (item) {
                    return !item?.sessionKey || item.sessionKey !== sessionKey
                });

                if (items.length) {
                    const item = items[items.length - 1];

                    ui.verificationAlertBody.removeClass('d-none');
                    ui.verificationAlertCount.html(formatCount(items.length));
                    ui.verificationAlertDate.html(formatDate(item.date));
                }
            }
        }

        function storeVerification() {
            const allItems = getVerificationItems();

            if (!allItems.hasOwnProperty(currentUuid)) {
                allItems[currentUuid] = [];
            }

            allItems[currentUuid].push({
                date: (currentDate).toISOString(),
                sessionKey: getSessionKey()
            });

            localStorage.setItem(LS_KEY_ITEMS, JSON.stringify(allItems));
        }

        function fetchVerificationHistory() {
            const clientHash = localStorage.getItem(LS_KEY_CLIENT_HASH)

            axios.get('{{ route('anketa.verification.history', $details->getFormUuid()) }}', {
                params: {
                    clientHash: clientHash,
                    date: (currentDate).toISOString()
                }
            })
                .then(function (response) {
                    if (!clientHash) {
                        localStorage.setItem(LS_KEY_CLIENT_HASH, response.data.clientHash)
                    }

                    const items = response.data.items

                    if (!items.length) {
                        return
                    }

                    items.forEach(function (item, index) {
                        let hint = ''
                        if (item.isCurrentDevice) {
                            hint = '<br><i>(с Вашего устройства)</i>'
                        }

                        ui.historyItems.append(`
                            <p class="text-center">
                                <b>${index + 1}.</b> ${item.date}
                                ${hint}
                            </p>
                        `)
                    })

                    ui.historyCount.html(items.length)
                    ui.historyWidget.removeClass('d-none')
                    ui.historyWidget.addClass('d-flex')
                })
        }

        $(document).ready(function () {
            const visitedSign = getVisitedSign()

            checkVerified();

            if (!visitedSign) {
                storeVerification();
                sessionStorage.setItem(SS_KEY_SIGN, JSON.stringify({visited: true}))
            }

            fetchVerificationHistory()
        })
    </script>

    <script>
        let isExpanded = false
        $('[href="#collapseVerificationHistory"]').click(function () {
            if (!isExpanded) {
                setTimeout(() => {
                    window.scrollBy(0, 50);
                }, 100)
            }

            isExpanded = !isExpanded
        })

        $(document).ready(function () {
            $('#form-verification-delete-link').click(function (e) {
                e.preventDefault();

                const url = $(this).attr('href');

                swal.fire({
                    title: 'Подтверждение',
                    text: 'Вы уверены, что хотите удалить осмотр?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Да',
                    cancelButtonText: 'Нет'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        })
    </script>

    <script>
        const phoneNumber = '{{ config('form_verification.phone') }}'
        const phoneLink = document.getElementById('phone')

        if (phoneNumber && phoneLink) {
            let hrefAttr = phoneNumber
                .replaceAll('(', '')
                .replaceAll(')', '')
                .replaceAll('-', '')
                .replaceAll(' ', '')

            hrefAttr = 'tel:+7' + hrefAttr.slice(1)

            phoneLink.innerHTML = phoneNumber
            phoneLink.href = hrefAttr
            phoneLink.classList.remove('d-none')
        }
    </script>
@endpush
