@extends('layouts.app')
<?php /** @var \Src\Terminals\Queries\GetSyncPageQuery\GetSyncPageResponse $response */ ?>

@section('title', 'Обновление настроек терминалов')
@section('sidebar', 1)

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if($response->isDefault())
                        <p><b>Обновление настроек терминалов по умолчанию</b></p>
                    @else
                        <p><b>Обновление настроек терминалов</b></p>
                    @endif
                    <div class="row mb-2">
                        <div class="col">
                            @foreach($response->getTerminals() as $terminal)
                                <span class="badge badge-rounded badge-secondary"> {{$terminal->getText()}} </span>
                            @endforeach
                        </div>
                    </div>
                    <main-settings />
                </div>
            </div>
        </div>
    </div>
@endsection

@push('setup-scripts')
    <script>
        window.PAGE_SETUP.settings = @json($response->getSettings()->toArray());
        window.PAGE_SETUP.terminals = @json($response->getTerminalsArray());
    </script>
@endpush
