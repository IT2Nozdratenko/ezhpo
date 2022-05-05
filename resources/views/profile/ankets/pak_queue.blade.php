@include('profile.ankets.components.pvs')

<input type="hidden" name="type_anketa" value="medic">
<input type="hidden" name="flag_pak" value="СДПО Р">

<div class="form-group row">
    <label class="col-md-3 form-control-label">ID водителя:</label>
    <article class="col-md-9">
        <input value="{{ $driver_id ?? '' }}" type="number" oninput="if(this.value.length >= 6) checkInputProp('hash_id', 'Driver', event.target.value, 'fio', $(event.target).parent())" required min="6" name="driver_id" class="MASK_ID_ELEM form-control">
        <div class="app-checker-prop"></div>
    </article>
</div>

<div class="cloning" id="cloning-first">
    <div class="form-group row">
        <label class="col-md-3 form-control-label">Дата и время осмотра:</label>
        <article class="col-md-9">
            <input min="1900-02-20T20:20"
                   max="2999-02-20T20:20" type="datetime-local" required value="{{ $default_current_date }}" name="anketa[0][date]" class="form-control">
        </article>
    </div>

    <div class="form-group row">
        <label class="col-md-3 form-control-label">Показания тонометра:</label>
        <article class="col-md-9">
            <input type="text" min="4" minlength="4" max="7" maxlength="7" placeholder="90/120 или 120/80 (пример)" name="anketa[0][tonometer]" value="{{ $tonometer ?? '' }}" class="form-control">
            <small>Недопустимо верхнее давление < 50 или > 220 , нижнее < 40 или > 160</small>
        </article>
    </div>

    <div class="anketa-delete"></div>
</div>

<div class="form-group row">
    <label class="col-md-3 form-control-label">Температура тела:</label>
    <article class="col-md-9">
        <input type="number" step="0.1" value="{{ $t_people ?? '' }}" name="t_people" class="form-control">
    </article>
</div>

<div class="form-group row">
    <label class="col-md-3 form-control-label">Проба на алкоголь:</label>
    <article class="col-md-9">
        <select name="proba_alko" required class="form-control">
            @isset($proba_alko)
                <option disabled selected value="{{ $proba_alko }}">{{ $proba_alko }}</option>
            @endisset

            <option selected value="Отрицательно">Отрицательно</option>
            <option value="Положительно">Положительно</option>
        </select>
    </article>
</div>

<div class="row">
    <div class="col-md-12">
        @if(isset($photos) || isset($videos))
            <p>Фотографии и видео:</p>
        @endif
    </div>
    @isset($photos)
        @if(!empty($photos))

                @foreach(explode(',', $photos) as $photo)
                    @php $isUri = strpos($photo, 'spdo.ta-7'); @endphp
                    @php $photo_path = $isUri ? $photo : Storage::url($photo); @endphp

                    <a href="{{ $photo_path }}" data-fancybox class="col-md-4">
                        <img width="100%" src="{{ $photo_path }}" alt="photo" />
                    </a>
                @endforeach
        @endif
    @endisset

    @isset($videos)
        @if(!empty($videos))
            @foreach(explode(',', $videos) as $video)
                <div class="col-md-4">
                    <video controls="controls" src="{{ $video }}" width="100%" height="100"></video>
                </div>
            @endforeach
        @endif
    @endisset
</div>

<hr>

@section('ankets_submit')
    <div class="text-center m-center">
        <label class="btn btn-success btn-sm">
            <i class="fa fa-check-circle"></i> Принять
            <input onchange="ANKETA_FORM.submit()" class="d-none" type="radio" value="Допущен" name="admitted" />
        </label>

        &nbsp;&nbsp;&nbsp;&nbsp;

        <label class="btn btn-danger btn-sm">
            <i class="fa fa-close"></i>
            Отклонить
            <input onchange="ANKETA_FORM.submit()" class="d-none" type="radio" value="Не допущен" name="admitted" />
        </label>
    </div>
@endsection

