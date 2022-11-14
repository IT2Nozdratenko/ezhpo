<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Редактирование элемента "{{ $title }}"</h4>
    </div>
@php
//dd($fields, $el->toArray())
@endphp
    <form action="{{ route('updateElement', ['type' => $model, 'id' => $id ]) }}" enctype="multipart/form-data" method="POST">
        @csrf

        <div class="modal-body">
            @foreach ($fields as $k => $v)
                @if($k == 'products_id' && user()->hasRole('client'))
                    @continue
                @endif
                @if($k == 'where_call_name' && !user()->access('companies_access_field_where_call_name'))
                    @continue
                @endif
                @if($k == 'where_call' && !user()->access('companies_access_field_where_call'))
                    @continue
                @endif
                @if($k === 'note' && $model === 'Company')
                        <div class="form-group" data-field="comment">
                            <label>
                                {{ $v['label'] }}
                            </label>
                            <textarea {{ user()->access('companies_access_field_note') ? '' : 'disabled' }}
                                   name="note"
                                   data-label="{{ $v['label'] }}"
                                   placeholder="{{ $v['label'] }}"
                                   data-field="Company_note" class="form-control">{{ $el[$k] ?? '' }}</textarea>
                        </div>
                    @continue
                @endif
                @php $is_required = isset($v['noRequired']) ? '' : 'required' @endphp
                @php if ($model === 'Instr' && $k === 'signature') continue; @endphp
                @php if ($k == 'essence') continue; @endphp

                    @if($k == 'unit' && ($model === 'Service' || $model === 'Product'))
                        <div data-field="essence" class="form-group">
                            <label><b class="text-danger text-bold">*</b>Сущности</label>
                            <select
                                name="essence"
                                data-label="Сущности"
                                class="filled-select2 filled-select"
                                required="required"
                            >
                                <option value="" selected>Не установлено</option>
                                @foreach(\App\Product::$essence as $essenceKey => $essenceName)
                                    <option value="{{ $essenceKey }}"
                                            @if ($el->essence !== null && $el->essence == $essenceKey) selected @endif>
                                        {{ $essenceName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                @if($k == 'contracts')
                    @php
                        $contractCollect = collect($el->contracts);
                    @endphp
                    <div data-field="contracts" class="form-group">
                        <label>Договор</label>
                        <select name="contracts[]"
                                data-label="Договоры"
                                class="js-chosen"
                                style="display: none;"
                                multiple="multiple"
                        >
{{--                            <option value=""  @if(!$contractCollect->count()) selected @endif>Не установлено</option>--}}
                            @foreach(\App\Models\Contract::whereNull('company_id')->orWhere('company_id', $el->id)->get(['id', 'name']) as $contract)
                                <option value="{{ $contract->id }}"
                                        @if ($contractCollect->where('id', $contract->id)->first()) selected @endif>
                                    {{ $contract->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                        <div class="">
                            <ul class="list-group">
                                @foreach($el->contracts as $contract)
                                <li class="list-group-item">{{ $contract->name }}
                                    @foreach($contract->services as $new_service)
                                        <ul class="list-group">
                                            <li class="list-group-item">{{ $new_service->name }}</li>
                                        </ul>
                                    @endforeach
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @continue
                @endif
                @if($k == 'contract_id' && ($model == 'Driver' || $model == 'Car'))
                        @php
                            $contractCollect = \App\Models\Contract::where('company_id', $el->company_id)->get(['id', 'name']);
                        @endphp
                    <div data-field="contract" class="form-group">
                        <label>Договор</label>
                        <select name="contract_id"
                                class="form-control"
                                data-label="Договор"
                                id="select_for_contract_driver_car"
                        >
                            <option value="" @if(!$el->contract_id) selected @endif>Не установлено</option>
                            @foreach($contractCollect as $contract)
                                <option value="{{ $contract->id }}"
                                        @if ($contract->id == $el->contract_id) selected @endif>
                                    {{ $contract->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <ul class="list-group">
                            <li class="list-group-item">{{ $el->contract->name }}
                            @foreach($el->contract->services as $new_service)
                                <ul class="list-group">
                                        <li class="list-group-item">{{ $new_service->name }}</li>
                                    </ul>
                                @endforeach
                            </li>
                        </ul>
                    </div>
                    @continue
                @endif

                @if($k !== 'id' && !isset($v['hidden']))
                    <div class="form-group" data-field="{{ $k }}">
                        <label>
                            @if($is_required) <b class="text-danger text-bold">*</b> @endif
                            {{ $v['label'] }}</label>

                        @include('templates.elements_field', [
                            'v' => $v,
                            'k' => $k,
                            'default_value' => $el[$k],
                            'element_id' => $el['id']
                        ])

                        {{--Синхронизация полей--}}
                        @if(isset($v['syncData']) && $model !== 'Company')
                            @foreach($v['syncData'] as $syncData)
                                <a href="{{ route('syncDataElement', [
                                    'model' => $syncData['model'],
                                    'fieldFind' => $syncData['fieldFind'],
                                    'fieldFindId' => $el['id'],
                                    'fieldSync' => $k,
                                    'fieldSyncValue' => $el[$k]
                                ]) }}" target="_blank" class="text-info btn-link"><i class="fa fa-spinner"></i> Синхронизация с: {{ $syncData['text'] }}</a>
                            @endforeach
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success">Сохранить</button>
        </div>
    </form>
</div>
