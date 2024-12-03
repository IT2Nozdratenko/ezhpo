<?php
return [
    /*
     * List fields all registries
     */
    'registries' => [
        'medic' => [
            'id' => 'ID записи',
            'company_name' => 'Место работы',
            'company_id' => 'ID компании',
            'date' => 'Дата и время осмотра',
            'period_pl' => 'Период выдачи ПЛ',
            'driver_fio' => 'ФИО работника',
            'date_prmo' => 'Дата ПРМО',
            'realy' => 'Осмотр реальный?',
            'driver_group_risk' => 'Группа риска',
            'type_view' => 'Тип осмотра',
            'proba_alko' => 'Признаки опьянения',
            'driver_gender' => 'Пол',
            'driver_year_birthday' => 'Дата рождения',
            'complaint' => 'Жалобы',
            'condition_visible_sliz' => 'condition_visible_sliz',
            'condition_koj_pokr' => 'Состояние кожных покровов',
            't_people' => 'Температура тела',
            'tonometer' => 'Артериальное давление',
            'pulse' => 'Пульс',
            'test_narko' => 'Тест на наркотики',
            'admitted' => 'Заключение о результатах осмотра',
            'protokol_path' => 'Протокол отстранения',
            'user_name' => 'ФИО ответственного',
            'user_eds' => 'ЭЦП медицинского работника',
            'operator_id' => 'ФИО оператора',
            'created_at' => 'Дата создания',
            'driver_id' => 'ID водителя',
            'photos' => 'Фото',
            'videos' => 'Видео',
            'med_view' => 'Мед показания',
            'pv_id' => 'Пункт выпуска',
            'flag_pak' => 'Флаг СДПО',
            'is_dop' => 'Режим ввода ПЛ'
        ],

        'tech' => [
            'id' => 'ID записи',
            'company_id' => 'ID Компании',
            'company_name' => 'Компания',
            'date' => 'Дата, время проведения контроля',
            'period_pl' => 'Период выдачи ПЛ',
            'created_at' => 'Дата создания',
            'date_prto' => 'Дата ПРТО',
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
            'is_dop' => 'Режим ввода ПЛ',
        ],

        'pak_queue' => [
            'id' => 'ID записи',
            'created_at' => 'Дата создания',
            'driver_fio' => 'Водитель',
            'pv_id' => 'Пункт выпуска',
            'tonometer' => 'Артериальное давление',
            'pulse' => 'Пульс',
            't_people' => 'Температура тела',
            'proba_alko' => 'Признаки опьянения',
            'complaint' => 'Жалобы',
            'admitted' => 'Заключение о результатах осмотра',
            'photos' => 'Фото',
            'videos' => 'Видео',
        ],

        'pak' => [
            'id' => 'ID записи',
            'date' => 'Дата и время осмотра',
            'user_name' => 'ФИО работника',
            'driver_gender' => 'Пол',
            'driver_year_birthday' => 'Дата рождения',
            'complaint' => 'Жалобы',
            'condition_visible_sliz' => 'Состояние видимых слизистых',
            'condition_koj_pokr' => 'Состояние кожных покровов',
            't_people' => 'Температура тела',
            'tonometer' => 'Артериальное давление',
            'pulse' => 'Пульс',
            'proba_alko' => 'Признаки опьянения',
            'admitted' => 'Заключение о результатах осмотра',
            'user_eds' => 'ЭЦП медицинского работника',
            'created_at' => 'Дата создания',
            'driver_group_risk' => 'Группа риска',
            'driver_fio' => 'Водитель',
            'company_id' => 'ID компании',
            'driver_id' => 'ID водителя',
            'photos' => 'Фото',
            'med_view' => 'Мед показания',
            'pv_id' => 'Пункт выпуска',
            'car_mark_model' => 'Автомобиль',
            'car_id' => 'ID автомобиля',
            'number_list_road' => 'Номер путевого листа',
            'type_view' => 'Тип осмотра',
            'comments' => 'Комментарий',
            'flag_pak' => 'Флаг СДПО',
        ],

        'report_cart' => [
            'id' => 'ID записи',
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

        'bdd' => [
            'company_name' => 'Компания',
            'company_id' => 'ID Компании',
            'date' => 'Дата, время',
            'created_at' => 'Дата внесения в журнал',
            'type_briefing' => 'Вид инструктажа',
            'briefing_name' => 'Название инструктажа',
            'driver_fio' => 'Ф.И.О водителя, прошедшего инструктаж',
            'user_name' => 'Ф.И.О (при наличии) лица, проводившего инструктаж',
            'pv_id' => 'Пункт выпуска',
            'user_eds' => 'Подпись лица, проводившего инструктаж',
            'driver_id' => 'ID водителя',
            'signature' => 'ЭЛ подпись водителя',
        ],

        'driver' => [
            'hash_id' => 'ID',
            'photo' => 'Фото',
            'fio' => 'ФИО',
            'year_birthday' => 'Дата рождения',
            'phone' => 'Телефон',
            'gender' => 'Пол',
            'group_risk' => 'Группа риска',
            'company_id' => 'Компания',
            'contracts' => 'Договоры',
            'services' => 'Услуги',
            'note' => 'Примечание',
            'procedure_pv' => 'Порядок выпуска',
            'date_bdd' => 'Дата БДД',
            'date_prmo' => 'Дата ПРМО',
            'snils' => 'СНИЛС',
            'driver_license' => 'Серия/номер ВУ',
            'driver_license_issued_at' => 'Дата выдачи ВУ',
            'date_driver_license' => 'Срок действия водительского удостоверения',
            'date_narcotic_test' => 'Дата тестирования на наркотики',
            'date_report_driver' => 'Дата снятия отчета с карты водителя',
            'time_card_driver' => 'Срок действия карты водителя',
            'town_id' => 'Город',
            'dismissed' => 'Уволен',
            'date_of_employment' => 'Дата устройства на работу',
            'pressure_systolic' => 'Порог верхнего давления',
            'pressure_diastolic' => 'Порог нижнего давления'
        ],

        'car' => [
            'hash_id' => 'ID',
            'gos_number' => 'Гос.номер',
            'mark_model' => 'Марка и модель',
            'type_auto' => 'Тип автомобиля',
            'contracts' => 'Договоры',
            'services' => 'Услуги',
            'trailer' => 'Прицеп',
            'company_id' => 'Компания',
            'note' => 'Примечание',
            'procedure_pv' => 'Порядок выпуска',
            'date_prto' => 'Дата ПРТО',
            'date_techview' => 'Дата техосмотра',
            'time_skzi' => 'Срок действия СКЗИ\настройки тахографа ЕСТР',
            'date_osago' => 'Дата ОСАГО',
            'town_id' => 'Город',
            'dismissed' => 'Уволен',
        ],

        'company' => [
            'hash_id' => 'ID',
            'name' => 'Название компании клиента',
            'crm' => 'Реестры',
            'journals' => 'Справочники',
            'note' => 'Примечание',
            'dismissed' => 'Черный список',
            'comment' => 'Комментарий',
            'user_id' => 'Ответственный',
            'req_id' => 'Реквизиты нашей компании',
            'pv_id' => 'ПВ',
            'town_id' => 'Город',
            'contracts' => 'Договоры',
            'services' => 'Услуги',
            'where_call' => 'Кому отправлять СМС при отстранении',
            'where_call_name' => 'Кому звонить при отстранении (имя, должность)',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН',
            'address' => 'Адрес',
            'procedure_pv' => 'Порядок выпуска',
            'has_actived_prev_month' => 'Были ли активны в прошлом месяце',
            'bitrix_link' => 'Ссылка на компанию в Bitrix24',
            'document_bdd' => 'Ссылка на таблицу с документами по бдд',
            'link_waybill' => 'Ссылка на ПЛ',
            'pressure_systolic' => 'Порог верхнего давления',
            'pressure_diastolic' => 'Порог нижнего давления'
        ],

        'product' => [
            'hash_id' => 'ID',
            'name' => 'Название',
            'type_product' => 'Тип',
            'unit' => 'Ед.изм.',
            'price_unit' => 'Стоимость за единицу',
            'type_anketa' => 'Реестр',
            'type_view' => 'Тип осмотра',
            'essence' => 'Сущности',
        ],

        'discount' => [
            'hash_id' => 'ID',
            'products_id' => 'Услуга',
            'trigger' => 'Триггер (больше/меньше)',
            'porog' => 'Пороговое значение',
            'discount' => 'Скидка (%)',
        ],

        'instr' => [
            'hash_id' => 'ID',
            'photos' => 'Фото',
            'name' => 'Название',
            'descr' => 'Описание',
            'type_briefing' => 'Вид инструктажа',
            'youtube' => 'Ссылка на YouTube\RUTUBE',
            'active' => 'Активен',
            'is_default' => 'Базовый',
            'sort' => 'Сортировка',
        ],

        'point' => [
            'hash_id' => 'ID',
            'name' => 'Пункт выпуска',
            'pv_id' => 'Город',
            'company_id' => 'Компания',
        ],

        'town' => [
            'hash_id' => 'ID',
            'name' => 'Город',
        ],

        'users' => [
            'hash_id' => 'ID',
            'photo' => 'Фото',
            'name' => 'ФИО',
            'login' => 'Логин',
            'email' => 'E-mail',
            'pv' => 'ПВ',
            'timezone' => 'GMT',
            'blocked' => 'Заблокирован',
            'roles' => 'Роль',
        ],

        'terminals' => [
            'status' => 'on/off',
            'hash_id' => 'ID',
            'name' => 'AnyDesk',
            'company_id' => 'Компания',
            'stamp_id' => 'Штамп',
            'town' => 'Город',
            'pv' => 'ПВ',
            'timez  one' => 'GMT',
            'blocked' => 'Заблокирован',
            'api_token' => 'Токен',
            'last_month_amount' => 'Количество осмотров за предыдущий месяц',
            'month_amount' => 'Количество осмотров за текущий месяц',
        ],

        'roles' => [
            'id' => 'ID',
            'guard_name' => 'Название',
        ],

        'ddates' => [
            'hash_id' => 'ID',
            'field' => 'Поле даты проверки',
            'days' => 'Кол-во дней',
            'action' => 'Действие',
        ],

        'pak_sdpo' => [
            'hash_id' => 'ID',
            'api_token' => 'Токен',
            'login' => 'Логин',
            'email' => 'E-mail',
            'pv_id' => 'ПВ',
            'company_id' => 'Компания',
            'timezone' => 'GMT',
            'blocked' => 'Заблокирован',
            'roles' => 'Роль',
        ],

        'req' => [
            'hash_id' => 'id',
            'name' => 'Название',
            'inn' => 'ИНН',
            'bik' => 'БИК',
            'kc' => 'К/С',
            'rc' => 'Р/С',
            'banks' => 'Банки',
            'director' => 'Должность руководителя',
            'director_fio' => 'ФИО Руководителя',
            'seal' => 'Печать',
        ],

        'field_prompts' => [
            'type' => 'Журнал',
            'name' => 'Поле',
            'content' => 'Подсказка',
        ],

        'pechat_pl' => [
            'id' => 'ID записи',
            'company_name' => 'Компания',
            'company_id' => 'ID Компания',
            'date' => 'Дата распечатки ПЛ',
            'driver_fio' => 'ФИО водителя',
            'count_pl' => 'Количество распечатанных ПЛ',
            'user_name' => 'Ф.И.О сотрудника, который готовил ПЛ',
            'user_eds' => 'ЭЦП сотрудника',
            'pv_id' => 'Пункт выпуска',
        ],

        'contracts' => [
            'id' => 'ID',
            'name' => 'Название',
            'main_for_company' => 'Главный',
            'finished' => 'Завершен',
            'services' => 'Услуги',
            'company' => 'Компания',
            'company.inn' => 'ИНН',
            'our_company.name' => 'Наша компания',
            'our_company.inn' => 'ИНН нашей компании',
            'date_of_start' => 'Дата начала договора',
            'date_of_end' => 'Дата окончания договора',
            'created_at' => 'Дата создания'
        ],

        'stamps' => [
            'id' => 'ID',
            'name' => 'Название',
            'company_name' => 'Заголовок',
            'licence' => 'Лицензия'
        ]
    ],

    /*
     * Default settings visible fields in journals
     */
    'visible' => [
        'medic' => [
            'date' => true,
            'driver_fio' => true,
            'period_pl' => true,
            'created_at' => true,
            'driver_group_risk' => true,
            'type_view' => true,
            'realy' => true,
            'proba_alko' => true,
            'test_narko' => true,
        ],
        'tech' => [
            'date' => true,
            'car_gos_number' => true,
            'period_pl' => true,
            'created_at' => true,
            'car_type_auto' => true,
            'type_view' => true,
            'realy' => true,
        ],
        'bdd' => [
            'date' => true,
            'driver_fio' => true,
            'type_briefing' => true,
            'company_name' => true,
            'created_at' => true,
            'user_name' => true,
        ],
        'pechat_pl' => [
            'date' => true,
            'driver_fio' => true,
            'count_pl' => true,
            'company_name' => true,
            'user_name' => true,
            'pv_id' => true,
        ],
        'report_cart' => [
            'date' => true,
            'driver_fio' => true,
            'company_name' => true,
            'user_name' => true,
        ],
        'pak' => [
            'id' => true,
            'date' => true,
            'user_name' => true,
            'driver_gender' => true,
            'driver_year_birthday' => true,
            'complaint' => true,
            'condition_visible_sliz' => true,
            'condition_koj_pokr' => true,
            't_people' => true,
            'tonometer' => true,
            'pulse' => true,
            'proba_alko' => true,
            'admitted' => true,
            'user_eds' => true,
            'created_at' => true,
            'driver_group_risk' => true,
            'driver_fio' => true,
            'company_id' => true,
            'driver_id' => true,
            'photos' => true,
            'med_view' => true,
            'pv_id' => true,
            'car_mark_model' => true,
            'car_id' => true,
            'number_list_road' => true,
            'type_view' => true,
            'comments' => true,
            'flag_pak' => true,
        ],
        'trip_tickets' => [
            'ticket_number' => true,
            'created_at' => true,
            'company_name' => true,
            'start_date' => true,
            'period_pl' => true,
            'validity_period' => true,
            'medic_form_id' => true,
            'driver_name' => true,
            'tech_form_id' => true,
            'car_number' => true,
            'logistics_method' => true,
            'transportation_type' => true,
            'template_code' => true,
        ],
    ],

    'client_exclude' => [
        'medic' => [
            'company_name',
            'company_id',
            'realy',
            'created_at',
            'flag_pak',
            'is_dop',
            't_people',
            'tonometer',
            'pulse',
            'period_pl',
            'date_prmo',
        ],
        'tech' => [
            'company_id',
            'company_name',
            'created_at',
            'realy',
            'is_dop',
            'date_prto',
            'period_pl'
        ],
        'bdd' => [
            'company_id',
            'company_name',
            'created_at',
        ],
        'trip_tickets' => [

        ],
    ]
];
