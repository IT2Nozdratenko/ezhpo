@extends('layouts.app')

@section('title', 'Терминалы')
@section('sidebar', 1)

@php
    $selectedTerminals = request()->get('hash_id') ?? [];
    $selectedPoints = request()->get('point_id') ?? [];
@endphp

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            @if($current_user_permissions['permission_to_view'])
                <form action="{{ route('terminals.v2.index') }}" method="GET">
                    @if(request()->get('deleted'))
                        <input type="hidden" value="1" name="deleted">
                    @endif

                    <div class="row mb-3">
                        <div class="col-lg-3">
                            <label for="hash_id">Терминал (ID/AnyDesk/SN):</label>
                            <select multiple
                                    name="hash_id[]"
                                    data-label="hash_id"
                                    data-field="Terminal_hash_id"
                                    data-allow-clear="true"
                                    class="filled-select2 filled-select select2-hidden-accessible"
                                    aria-hidden="true">
                                <option value="" data-select2-id="8">Не установлено</option>

                                @foreach($terminals as $option)
                                    <option
                                            @if(in_array($option['id'], $selectedTerminals)) selected @endif
                                    value="{{ $option['id'] }}">
                                        {{ $option['text'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label for="pv_id">ПВ:</label>
                            <select multiple
                                    name="point_id[]"
                                    data-label="id"
                                    data-field="Points_id"
                                    data-allow-clear="true"
                                    class="filled-select2 filled-select select2-hidden-accessible"
                                    aria-hidden="true">

                                @foreach($points ?? [] as $option)
                                    <option
                                            @if(in_array($option['id'], $selectedPoints)) selected @endif
                                    value="{{ $option['id'] }}">
                                        {{ $option['text'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 form-group">
                            <label for="town_id">Город:</label>
                            @include('templates.elements_field', [
                                'v' => [
                                    'label' => 'Город',
                                    'type' => 'select',
                                    'values' => 'Town',
                                    'getField' => 'name',
                                    'getFieldKey' => 'id',
                                    'multiple' => true,
                                    'trashed' => true,
                                    'concatField' => 'hash_id',
                                    'orderBy' => 'name'
                                ],
                                'k' => 'town_id',
                                'is_required' => '',
                                'model' => 'Company',
                                'default_value' => request()->get('town_id')
                            ])
                        </div>

                        <div class="col-lg-3 form-group">
                            <label for="company_id">Компания:</label>
                            @include('templates.elements_field', [
                                'v' => [
                                    'label' => 'Компания',
                                    'type' => 'select',
                                    'values' => 'Company',
                                    'noRequired' => 1,
                                    'getFieldKey' => 'id',
                                    'multiple' => true,
                                    'concatField' => 'hash_id',
                                    'orderBy' => 'name'
                                ],
                                'k' => 'company_id',
                                'is_required' => '',
                                'model' => 'Company',
                                'default_value' => request()->get('company_id')
                            ])
                        </div>

                        <div class="col-lg-3">
                            <label for="date_check">Срок поверки:</label>
                            <input type="date"
                                   value="{{ request()->get('date_check') }}"
                                   name="date_check"
                                   class="form-control"/>
                        </div>

                        <div class="col-lg-3">
                            <label for="TO_date_check">Срок поверки до:</label>
                            <input type="date"
                                   value="{{ request()->get('TO_date_check') }}"
                                   name="TO_date_check"
                                   class="form-control"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-2 form-group">
                            <input type="submit" class="btn btn-success btn-sm" value="Поиск">
                            <a href="{{ route('terminals.v2.index') }}" class="btn btn-danger btn-sm">Сбросить</a>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <terminals-index-widget
            :deleted='{{ request()->get('deleted', 0) }}'
            :current_user_permissions='@json($current_user_permissions)'
            :points='@json($pointsToTable)'
            :fields='@json($fields)'
            :devices-options='@json($devicesOptions)'
    ></terminals-index-widget>
@endsection

@push('setup-scripts')
    <script>
      window.PAGE_SETUP = {
        LOGS_MODAL: {
          tableDataUrl: '{{ route('logs.list-model') }}',
          mapDataUrl: '{{ route('logs.list-model-map') }}',
          model: '{{ 'users' }}',
        },
        MODEL_SEARCHER: {
          tableDataUrl: '{{ route('searchElement') }}',
        }
      };
    </script>
@endpush
