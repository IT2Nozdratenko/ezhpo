<input
    @if(isset($v['saveToHistory']) && $v['type'] !== 'file')
        onchange="addFieldToHistory(event.target.value, '{{ $v['label'] }}');"
    @endif

    @if($v['type'] == 'file' && isset($v['resize']))
        id="croppie-input{{ $uniqueInputId }}"
    @endif

    value="{{ $default_value }}"
    type="{{ $v['type'] }}" {{ $is_required }}
    name="{{ $k }}"
    data-label="{{ $v['label'] ?? $k }}"
    placeholder="{{ $v['label'] }}"
    data-field="{{ $model }}_{{ $k }}"
    @if ($v['type'] !== 'file') class="form-control {{ $v['classes'] ?? '' }}" @endif
/>

@if($v['type'] == 'file' && $default_value)
    <div>
        <a data-fancybox href="{{ Storage::url($default_value) }}" target="_blank">
            <i class="fa fa-search"></i> Просмотреть файл
        </a>
        <br/>
        <a href="{{ route('deleteFileElement', [
                'id' => $element_id ?? 0,
                'field' => $k,
                'model' => $model
            ]) }}">
            <i class="fa fa-trash"></i> Удалить файл
        </a>
    </div>

    @if(isset($v['resize']))
    <div style="display: none;" id="croppie-block{{ $uniqueInputId }}" class="croppie-block text-center">
        <input type="hidden" name="{{ $k }}_base64" id="croppie-result-base64{{ $uniqueInputId }}">
        <div class="croppie-demo" data-croppie-id="{{ $uniqueInputId }}"></div>
        <button type="button" data-croppie-id="{{ $uniqueInputId }}" class="btn croppie-save btn-sm btn-success">Сохранить обрезку</button>
        <button type="button" data-croppie-id="{{ $uniqueInputId }}" class="btn croppie-delete btn-sm btn-danger">Удалить фото</button>
    </div>
    @endif
@endif
