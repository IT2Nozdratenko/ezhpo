@extends('layouts.app')

@section('title', $title)
@section('sidebar', 1)
@section('class-page', 'page-anketa anketa-' . $type_anketa)

@php
    if (!isset($is_dop)) {
        $is_dop = isset($_GET['is_dop']) ? ($_GET['is_dop'] === "1") : 0;
    }

@endphp

@section('custom-scripts')
    <script type="text/javascript">
        if(screen.width <= 700) {
            ANKETA_FORM_VIEW.insertBefore(ANKETA_FORM_ROOT, ANKETA_FORM_VIEW_FIRST)
        }

        let notAdmittedReasons = @json($not_admitted_reasons ?? []);
        notAdmittedReasons.filter((reason) => ['tonometer', 'proba_alko', 't_people'].includes(reason))

        notAdmittedReasons.forEach((notAdmittedReason) => {
            let input

            switch (notAdmittedReason) {
                case 'tonometer':
                    input = $('input[name="anketa[0][tonometer]"]')
                    break;
                case 'proba_alko':
                    input = $('select[name="proba_alko"]')
                    break;
                case 't_people':
                    input = $('input[name="t_people"]')
                    break;
            }

            if (input) {
                input.css("background", "pink")
            }
        })

        let needApproveAdmitting = notAdmittedReasons.length > 0
        function approveAdmitting() {
            const admitted = $("input[name='admitted']:checked");

            if (!needApproveAdmitting || !admitted || (admitted.val() !== 'Допущен')) {
                $('#ANKETA_FORM').trigger('submit')

                return
            }

            window.swal.fire({
                title: 'Отклонение от параметров!',
                text: 'Обратите внимание, у водителя имеются отклонения от установленных предельных параметров. Подтвердите действие.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Допустить водителя',
                cancelButtonText: "Отмена",
            }).then(function(result) {
                if (result.isConfirmed) {
                    $('#ANKETA_FORM').trigger('submit')
                } else {
                    admitted.removeAttr('checked')
                }
            })
        }
    </script>
@endsection

@section('content')

<div class="row" data-anketa="{{ $anketa_view }}" id="ANKETA_FORM_VIEW">
    <!-- Анкета -->
    <div class="col-lg-2" id="ANKETA_FORM_VIEW_FIRST">
        <div class="card">
            <div class="card-body">
                <p><b>Карточка автомобиля</b></p>

                <div id="CARD_CAR">
                    Не найдено
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6" id="ANKETA_FORM_ROOT">
        <div class="card">
            <div class="card-body">

                <h3 class="text-center">{{ $title }}</h3>
                <hr>

                <!-- Анкета: {{ $title }} -->
                <article class="anketa anketa-fields">
                    @isset($_GET['errors'])
                        @foreach($_GET['errors'] as $error)
                            <div class="alert alert-danger" role="alert">{{ $error }}</div>
                        @endforeach
                    @endisset

                    @isset($_GET['createdId'])
                        <div class="row">
                            @foreach($_GET['createdId'] as $cId)
                                @php $anketa = \App\Anketa::find($cId) @endphp


                                @isset($anketa)
                                    <div class="col-md-6">
                                        <div class="card p-2 text-xsmall">
                                            @if($type_anketa === 'medic')
                                                <b>"{{ $title }}" успешно создан!</b>
                                                <br/> ID осмотра: {{ $cId }}

                                                @isset($anketa->driver_id)
                                                    @isset(\App\Driver::where('hash_id', $anketa->driver_id)->first()->fio)
                                                        <br /> <b>Водитель: {{ \App\Driver::where('hash_id', $anketa->driver_id)->first()->fio }}</b>
                                                    @endisset
                                                @endisset

                                                @if($anketa->date)
                                                    <div>
                                                        <i>Дата проведения осмотра: <br/><b>{{ $anketa->date }}</b></i>
                                                    </div>
                                                @elseif($anketa->period_pl)
                                                    <div>
                                                        <i>Период проведения осмотра: <br/><b>{{ $anketa->period_pl }}</b></i>
                                                    </div>
                                                @endif

                                                @if($anketa->admitted === 'Не допущен')
                                                    @if(user()->access('medic_closing_edit'))
                                                        <a class="btn primary btn-sm btn-table"
                                                           href="{{ route('docs.get', ['type' => 'closing', 'anketa_id' => $cId]) }}">
                                                            Мед. заключение
                                                        </a>
                                                    @endif
                                                @endif
                                            @else
                                                <b>"{{ $title }}" (ID: {{ $cId }}) успешно создан!</b>

                                                @isset($anketa->driver_id)
                                                    @isset(\App\Driver::where('hash_id', $anketa->driver_id)->first()->fio)
                                                        <br /> <b>Водитель: {{ \App\Driver::where('hash_id', $anketa->driver_id)->first()->fio }}</b>
                                                    @endisset
                                                @endisset

                                                @isset($anketa->car_id)
                                                    @isset(\App\Driver::where('hash_id', $anketa->car_id)->first()->gos_number)
                                                        <br /> <b>Госномер автомобиля: {{ \App\Driver::where('hash_id', $anketa->car_id)->first()->gos_number }}</b>
                                                    @endisset
                                                @endisset

                                                <div>
                                                    Дата проведения осмотра <b>{{ $anketa->date }}</b>
                                                </div>
                                            @endif

                                            @isset($_GET['redDates'])
                                                @if(is_array($_GET['redDates']))
                                                    @if(count($_GET['redDates']) > 0)
                                                        @foreach($_GET['redDates'] as $redDateKey => $redDateVal)
                                                            <p class="text-danger">
                                                                {{ __('ankets.'.$redDateKey) }}: {{ $redDateVal['value'] }}
                                                            </p>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            @endisset

                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endisset

                    @isset($_GET['msg'])
                        <div class="alert alert-success">
                            <b>{{ $_GET['msg'] }}</b>
                        </div>
                    @endif

                    <form method="POST"
                          @if(isset($anketa_route))
                            action="{{ route($anketa_route, $id) }}"
                          @else
                            action="{{ route('addAnket') }}"
                          @endif
                          class="form-horizontal"
                          onsubmit="document.querySelector('#page-preloader').classList.remove('hide')"
                          enctype="multipart/form-data"
                          id="ANKETA_FORM"
                    >
                        @csrf

                        @if(isset($anketa_route) && $id)
                            <input type="hidden" name="REFERER" value="{{ url()->previous() }}">
                        @endif

                        @include($anketa_view)

                        @if(!isset($anketa_route))
                            <div id="cloning-append"></div>

                            <button type="button" id="ANKETA_CLONE_TRIGGER" class="anketa__addnew">
                                <i class="fa fa-plus"></i>
                            </button>
                        @endif

                        <div class="form-group row">
                            @hasSection('ankets_submit')
                                @yield('ankets_submit')
                            @else
                                @if (isset($anketa_route))
                                    <a href="{{ url()->previous()  }}" class="m-center btn btn-info">{{ __('Вернуться в журнал') }}</a>
                                @endif
                                <button type="submit"  class="m-center btn btn-success">{{ __('ankets.submit') }}</button>
                            @endif
                        </div>
                    </form>
                </article>

            </div>
        </div>
    </div>

    <div class="col-lg-2">
        <div class="card">
            <div class="card-body">
                <p><b>Карточка компании</b></p>

                <div id="CARD_COMPANY">
                    Не найдено
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2">
        <div class="card">
            <div class="card-body">
                <p><b>Карточка водителя</b></p>

                <div id="CARD_DRIVER">
                    Не найдено
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
