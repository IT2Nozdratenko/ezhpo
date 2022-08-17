@extends('layouts.app')

@section('title', 'ЛК Водителя')
@php
$instrs = $instrs->sortBy('id')
            ->sort(function ($a, $b) {
        if (is_null($a->sort)) {
            return is_null($b->sort) ? 0 : 1;
        }
        if (is_null($b->sort)) {
            return -1;
        }
        if ($a->sort == $b->sort) {
            return 0;
        }

        return $a->sort < $b->sort ? -1 : 1;
});
@endphp
@section('content')
    <div class="container text-center">
        <h1>БДД</h1>
        <hr>

        <div class="row">
            <div class="col-md-12 mb-5">
                <h2>Выберите инструктаж</h2>
                <hr>

                <div class="row">
                    <div class="col-md-4 nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        @foreach($instrs->sortBy('sort') as $k => $instr)
                            <a class="nav-link" id="instr-{{ $instr->id }}" data-toggle="pill" href="#instr-{{ $instr->id }}-tab" role="tab" aria-controls="instr-{{ $instr->id }}" aria-selected="false">{{ $instr->name }} [{{ $instr->type_briefing }}]</a>
                        @endforeach
                    </div>
                    <div class="col-md-8 tab-content text-left" id="v-pills-tabContent">
                        @foreach($instrs as $k => $instr)
                            <div class="tab-pane fade" id="instr-{{ $instr->id }}-tab" role="tabpanel" aria-labelledby="instr-{{ $instr->id }}-tab">
                                <h3>{{ $instr->name }}</h3>
                                <h6>Тип инструктажа: {{ $instr->type_briefing }}</h6>

                                @if($instr->photos)
                                    <p>
                                        <img src="{{ Storage::url($instr->photos) }}" width="200">
                                    </p>
                                @endif

                                <iframe width="100%" height="300" src="https://www.youtube.com/embed/{{ str_replace('https://www.youtube.com/watch?v=', '', $instr->youtube) }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                <a href="{{ $instr->youtube }}">Посмотреть видео на YouTube</a>
                                <br><br>
                                <p>{{ $instr->descr }}</p>

                                <hr>
                                <h4>Вам был понятен инструктаж?</h4>

                                <div class="row">
                                    <div class="col-md-1">
                                        <form id="API_FORM_ANKETS_{{ $instr->id }}" data-success-title="Инструктаж пройден!" class="API_FORM_SEND" method="post" action="{{ route('api.addform') }}">
                                            @csrf

                                            <input type="hidden" name="type_anketa" value="bdd" />
                                            <input type="hidden" name="pv_id" value="{{ $pv_id }}" />
                                            <input value="{{ auth()->user()->login }}" type="hidden" name="driver_id">
                                            <input value="{{ $instr->type_briefing }}" type="hidden" name="anketa[0][type_briefing]">
                                            <input type="hidden"  value="{{ date('Y-m-d\TH:i', time()) }}" name="anketa[0][date]">
                                            <input type="hidden"  value="Подписано простой ЭЦП" name="anketa[0][signature]">

                                            <button type="submit" class="btn btn-success">Да</button>
                                        </form>

                                    </div>

                                    <div class="col-md-1">
                                        <a href="{{ route('page.driver') }}" onclick="return confirm('Точно хотите уйти?')"  class="btn btn-danger">Нет</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
