@extends('layouts.app')

@section('title','Системные настройки')
@section('sidebar', 1)

@section('content')
<div class="row">
    <!-- Form Elements -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.update') }}"
                      method="POST" class="form-horizontal"
                      enctype="multipart/form-data"
                >
                    @csrf
                    <div class="row px-4">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <div class="logo d-flex justify-content-center pb-2"
                                    style="max-height: 80px; overflow: hidden"
                                >
                                    <img src="{{ Storage::url($logo) }}"
                                         alt=""
                                         width="300px"
                                         style="height: 100%;"
                                    >
                                </div>
                                <label for="slogo-input" class="ml-1 mt-2">Логотип</label>
                                <div class="custom-file">
                                    <input
                                        type="file"
                                        class="custom-file-input "
                                        accept="image/*"
                                        id="logo-input"
                                        name="logo"
                                    >
                                    <label class="custom-file-label" for="logo-input">Выберите файл</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9 row">
                            <div class="form-group col-lg-6">
                                <label for="sms_api_key" class="mb-1">API ключ sms.ru</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sms_api_key_prepend">
                                            <i class="fa fa-key"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                           class="form-control"
                                           id="sms_api_key"
                                           style="height: auto"
                                           placeholder="Введите API ключ"
                                           name="sms_api_key"
                                           aria-describedby="sms_api_key_prepend"
                                           value="{{ $sms_api_key }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="sms_text_phone" class="mb-1">Телефон, куда звонить в случае вопросов</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sms_api_key_prepend">
                                            <i class="fa fa-phone"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                           class="form-control"
                                           id="sms_api_key"
                                           style="height: auto"
                                           placeholder="Введите телефон"
                                           name="sms_text_phone"
                                           aria-describedby="sms_text_phone_prepend"
                                           value="{{ $sms_text_phone }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="phone" class="mb-1">Телефон на странице авторизации</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sms_api_key_prepend">
                                            <i class="fa fa-phone"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                           class="form-control"
                                           id="phone"
                                           style="height: auto"
                                           placeholder="Введите телефон"
                                           name="phone"
                                           aria-describedby="sms_text_phone_prepend"
                                           value="{{ $phone }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="telegram" class="mb-1">Telegram ссылка</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sms_api_key_prepend">
                                            <i class="fa fa-link"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                           class="form-control"
                                           id="telegram"
                                           style="height: auto"
                                           placeholder="Введите ссылку на телеграм"
                                           name="telegram"
                                           aria-describedby="sms_text_phone_prepend"
                                           value="{{ $telegram }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-12">
                                <label for="not_identify_text" class="mb-1">Текст при нажатии кнопки "Не идентифицирован"</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="not_identify_text_prepend">
                                            <i class="fa fa-copy"></i>
                                        </span>
                                    </div>
                                    <textarea
                                        class="form-control"
                                        id="not_identify_text"
                                        style="height: auto"
                                        placeholder="Введите Текст сообщения пользователю СДПО"
                                        name="not_identify_text"
                                        aria-describedby="not_identify_text_prepend"
                                    >{{ $not_identify_text }}</textarea>
                                </div>
                            </div>

                            <div class="form-group col-lg-12">
                                <label for="sms_text_driver" class="mb-1">Текст SMS для Водителя при непрохождении осмотра</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sms_text_driver_prepend">
                                            <i class="fa fa-copy"></i>
                                        </span>
                                    </div>
                                    <textarea
                                        class="form-control"
                                        id="sms_text_driver"
                                        style="height: auto"
                                        placeholder="Введите Текст SMS"
                                        name="sms_text_driver"
                                        aria-describedby="sms_text_driver_prepend"
                                    >{{ $sms_text_driver }}</textarea>
                                </div>
                            </div>

                            <div class="form-group col-lg-12">
                                <label for="sms_text_car" class="mb-1">Текст SMS для Авто при непрохождении осмотра</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sms_text_car_prepend">
                                            <i class="fa fa-copy"></i>
                                        </span>
                                    </div>
                                    <textarea
                                        class="form-control"
                                        id="sms_text_car"
                                        style="height: auto"
                                        placeholder="Введите Текст SMS"
                                        name="sms_text_car"
                                        aria-describedby="sms_text_car_prepend"
                                    >{{ $sms_text_car }}</textarea>
                                </div>
                            </div>

                            <div class="form-group col-lg-12">
                                <label for="sms_text_default" class="mb-1">Текст сообщения по умолчанию</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sms_text_default_prepend">
                                            <i class="fa fa-copy"></i>
                                        </span>
                                    </div>
                                    <textarea
                                        class="form-control"
                                        id="sms_text_default"
                                        style="height: auto"
                                        name="sms_text_default"
                                        placeholder="Введите Текст SMS"
                                        aria-describedby="sms_text_default_prepend"
                                    >{{ $sms_text_default }}</textarea>
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="pressure_systolic" class="mb-1">Верхний порого А/Д</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="pressure_systolic">
                                            <i class="fa fa-sort-numeric-asc"></i>
                                        </span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           id="pressure_systolic"
                                           style="height: auto"
                                           placeholder="Введите пороговое давление"
                                           name="pressure_systolic"
                                           aria-describedby="pressure_systolic"
                                           value="{{ $pressure_systolic }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="pressure_diastolic" class="mb-1">Нижний порого А/Д</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="pressure_diastolic">
                                            <i class="fa fa-sort-numeric-asc"></i>
                                        </span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           id="pressure_diastolic"
                                           style="height: auto"
                                           placeholder="Введите пороговое давление"
                                           name="pressure_diastolic"
                                           aria-describedby="pressure_diastolic"
                                           value="{{ $pressure_diastolic }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="pulse_upper" class="mb-1">Верхний порог пульса</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="pulse_upper">
                                            <i class="fa fa-sort-numeric-asc"></i>
                                        </span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           id="pulse_upper"
                                           style="height: auto"
                                           placeholder="Введите верхний порог пульса"
                                           name="pulse_upper"
                                           aria-describedby="pulse_upper"
                                           value="{{ $pulse_upper }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="pulse_lower" class="mb-1">Нижний порог пульса</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="pulse_lower">
                                            <i class="fa fa-sort-numeric-asc"></i>
                                        </span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           id="pulse_lower"
                                           style="height: auto"
                                           placeholder="Введите нижний порог пульса"
                                           name="pulse_lower"
                                           aria-describedby="pulse_lower"
                                           value="{{ $pulse_lower }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="time_of_alcohol_ban" class="mb-1">Время запрета на прохождение повторного обследования при положительной пробе на алкоголь, (мин.)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="time_of_alcohol_ban">
                                            <i class="fa fa-clock-o"></i>
                                        </span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           id="time_of_alcohol_ban"
                                           style="height: auto"
                                           placeholder="Введите время запрета"
                                           name="time_of_alcohol_ban"
                                           aria-describedby="time_of_alcohol_ban"
                                           value="{{ $time_of_alcohol_ban }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="time_of_pressure_ban" class="mb-1">Время запрета на прохождение повторного обследования при отстранении по давлению, (мин.)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="time_of_pressure_ban">
                                            <i class="fa fa-clock-o"></i>
                                        </span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           id="time_of_pressure_ban"
                                           style="height: auto"
                                           placeholder="Введите время запрета"
                                           name="time_of_pressure_ban"
                                           aria-describedby="time_of_pressure_ban"
                                           value="{{ $time_of_pressure_ban }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="timeout" class="mb-1">Время ожидания ручного режима</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="timeout">
                                            <i class="fa fa-times"></i>
                                        </span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           id="timeout"
                                           style="height: auto"
                                           placeholder="Введите ожидания ручного режима"
                                           name="timeout"
                                           aria-describedby="timeout"
                                           value="{{ $timeout }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="timeout" class="mb-1">Телефон поддержки для СДПО</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="sdpo_support_phone">
                                            <i class="fa fa-phone"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                           class="form-control"
                                           id="sdpo_support_phone"
                                           style="height: auto"
                                           placeholder="Введите телефон"
                                           name="sdpo_support_phone"
                                           aria-describedby="timeout"
                                           value="{{ $sdpo_support_phone }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group col-12">
                                <button type="submit" class="btn btn-success float-right">Сохранить</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
