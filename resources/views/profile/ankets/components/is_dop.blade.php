<div class="form-group row col-md-12">
    <label class="form-control-label">Режим ввода ПЛ:</label>
    <article class="ml-3">
        <a class="text-small" href="{{ route('forms', ['type' => $type_anketa, 'is_dop' => !$is_dop]) }}">

            <input onchange="this.parentNode.click()" type="checkbox" @if($is_dop) checked @endif>

            {{ isset($is_dop) ? ($is_dop ? 'Выкл' : 'Вкл') : 'Вкл' }}

        </a>

        <input type="hidden" name="is_dop" value="{{ $is_dop ?? 0 }}">
    </article>
</div>
