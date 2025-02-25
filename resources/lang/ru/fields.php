<?php

return [
    'medic' => [
        'date' => 'Дата и время осмотра',
        'driver_fio' => 'Период выдачи ПЛ',
        'period_pl' => 'Период пд',
        'created_at' => 'Дата создания',
        'driver_group_risk' => 'Группа риска',
        'type_view' => 'Тип осмотра',
        'realy' => 'Осмотр реальный?',
        'proba_alko' => 'Признаки опьянения',
        'test_narko' => 'Тест на наркотики',
        'company_name' => 'Компания',
        'company_id' => 'ID Компании',
        'pv_id' => 'Пункт выпуска',
        'driver_id' => 'ID водителя',
        'user_name' => 'ФИО ответственного',
        'driver_gender' => 'Пол',
        'driver_year_birthday' => 'Дата рождения',
        'complaint' => 'Жалобы',
        'condition_visible_sliz' => 'Состояние видимых слизистых',
        'condition_koj_pokr' => 'Состояние кожных покровов',
        't_people' => 'Температура тела',
        'tonometer' => 'Артериальное давление',
        'pulse' => 'Пульс',
        'admitted' => 'Заключение о результатах осмотра',
        'user_eds' => 'ЭЦП медицинского работника',
        'photos' => 'Фото',
        'videos' => 'Видео',
        'med_view' => 'Мед показания',
        'flag_pak' => 'Флаг СДПО',
        'is_dop' => 'Неполный осмотр',
    ],
    'tech' => [
        'company_id' => 'ID Компании',
        'company_name' => 'Компания',
        'date' => 'Дата, время проведения контроля',
        'period_pl' => 'Период выдачи ПЛ',
        'created_at' => 'Дата создания',
        'car_gos_number' => 'Гос.регистрационный номер ТС',
        'realy' => 'Осмотр реальный?',
        'car_type_auto' => 'Категория ТС',
        'car_mark_model' => 'Марка автомобиля',
        'type_view' => 'Тип осмотра',
        'driver_fio' => 'ФИО Водителя',
        'driver_id' => 'ID водителя',
        'car_id' => 'ID автомобиля',
        'number_list_road' => 'Номер ПЛ',
        'odometer' => 'Показания одометра',
        'point_reys_control' => 'Отметка о прохождении контроля',
        'user_name' => 'ФИО ответственного',
        'user_eds' => 'Подпись лица, проводившего контроль',
        'pv_id' => 'Пункт выпуска',
        'is_dop' => 'Неполный осмотр',
    ],
    'Dop' => [
        'date' => 'Дата и время выдачи пл',
        'company_name' => 'Компания',
        'driver_fio' => 'ФИО водителя',
        'car_mark_model' => 'Марка автомобиля',
        'car_gos_number' => 'Государственный регистрационный номер транспортного средства',
        'company_id' => 'ID компании',
        'driver_id' => 'ID водителя',
        'car_id' => 'ID автомобиля',
        'number_list_road' => 'Номер путевого листа',
        'pv_id' => 'Пункт выпуска',
        'user_name' => 'ФИО ответственного',
        'user_eds' => 'ЭЦП контролера',
        'created_at' => 'Дата/Время создания записи',
    ],

    'report_cart' => [
        'company_id' => 'ID Компании',
        'company_name' => 'Компания',
        'date' => 'Дата снятия отчета',
        'driver_fio' => 'Ф.И.О водителя',
        'user_name' => 'Ф.И.О (при наличии) лица, проводившего снятие',
        'user_eds' => 'Подпись лица, проводившего снятие',
        'pv_id' => 'Пункт выпуска',
        'driver_id' => 'ID водителя',
        'signature' => 'ЭЛ подпись водителя',
        'created_at' => 'Дата/Время создания записи',
    ],

    'pechat_pl' => [
        'company_name' => 'Компания',
        'company_id' => 'ID Компания',
        'date' => 'Дата распечатки ПЛ',
        'driver_fio' => 'ФИО водителя',
        'count_pl' => 'Количество распечатанных ПЛ',
        'user_name' => 'Ф.И.О сотрудника, который готовил ПЛ',
        'user_eds' => 'ЭЦП сотрудника',
        'pv_id' => 'Пункт выпуска',
        'created_at' => 'Дата создания',
    ],

    'bdd' => [
        'company_name' => 'Компания',
        'company_id' => 'ID Компании',
        'date' => 'Дата, время',
        'created_at' => 'Дата внесения в журнал',
        'type_briefing' => 'Вид инструктажа',
        'driver_fio' => 'Ф.И.О водителя, прошедшего инструктаж',
        'user_name' => 'Ф.И.О (при наличии) лица, проводившего инструктаж',
        'pv_id' => 'Пункт выпуска',
        'user_eds' => 'Подпись лица, проводившего инструктаж',
        'driver_id' => 'ID водителя',
        'signature' => 'ЭЛ подпись водителя',
    ],
];
