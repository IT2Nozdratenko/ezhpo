@foreach ($fields as $k => $v)
    @php if ($k == 'hash_id') continue; @endphp
    @if($k == 'products_id' && user()->hasRole('client'))
        @continue
    @endif
    @if($k == 'where_call_name' && !user()->access('companies_access_field_where_call_name'))
        @continue
    @endif
    @if($k == 'where_call' && !user()->access('companies_access_field_where_call'))
        @continue
    @endif

    @if($k == 'contracts')
        @continue
    @endif

    @if($k == 'reqs_validated')
        @continue
    @endif

    @if($k == 'one_c_synced')
        @continue
    @endif

    @if($k == 'contract_id' && ($model == 'Driver' || $model == 'Car'))
        @continue
    @endif

    @if($k == 'procedure_pv' && user()->hasRole(['medic', 'tech']))
        <div class="form-group" data-field="comment">
            <label for="disabledProcedureInput">{{$v['label']}}</label>
            <input type="text" id="disabledProcedureInput" class="form-control"
                   placeholder="Закрыто для редактирования" disabled>
        </div>
        @continue
    @endif

    @php
        $is_required = isset($v['noRequired']) ? '' : 'required';
        $default_value = $v['defaultValue'] ?? '';
        $disabled = false;

        if (user()->hasRole('client')) {
            if ($model === 'Driver' && in_array($k, ['group_risk', 'note', 'procedure_pv', 'pressure_systolic', 'pressure_diastolic', 'only_offline_medic_inspections'])) {
                $disabled = true;
            }

            if ($model === 'Car' && in_array($k, ['note', 'procedure_pv'])) {
                $disabled = true;
            }
        }
    @endphp

    @if($k === 'year_birthday' && $model === 'Driver')
        <div class="form-group" data-field="{{ $k }}">
            <label>
                <b class="text-danger text-bold">* </b>
                {{ $v['label'] }}
            </label>

            <input
                type="{{ $v['type'] }}" {{ $is_required }}
                name="{{ $k }}"
                data-label="{{ $v['label'] ?? $k }}"
                placeholder="{{ $v['label'] }}"
                data-field="{{ $model }}_{{ $k }}"
                class="form-control {{ $v['classes'] ?? '' }}"
                min="{{ \Carbon\Carbon::now()->subYears(80)->format('Y-m-d') }}"
                max="{{ \Carbon\Carbon::now()->subYears(16)->format('Y-m-d') }}"
            />
        </div>
        @continue
    @endif

    @if($k !== 'id' && !isset($v['hidden']))
        @if($model === 'Instr' && $k === 'sort')
            <div class="form-group" data-field="{{ $k }}">
                <label>
                    @if($is_required)
                        <b class="text-danger text-bold">* </b>
                    @endif
                    {{ $v['label'] }}
                </label>

                @if ($v['label'] == 'ФИО')
                    dd($v);
                @endif
                @include('templates.elements_field')
            </div>
            <!-- Сортировка инструктажей доступна админу или инженеру -->
        @elseif($model === 'Instr' && $k === 'signature')
        @else
            <div class="form-group" data-field="{{ $k }}">
                <label>
                    @if($is_required)
                        <b class="text-danger text-bold">* </b>
                    @endif
                    {{ $v['label'] }}
                </label>

                @include('templates.elements_field')
            </div>
        @endif
    @endif
@endforeach

@php $disabled = false; @endphp
