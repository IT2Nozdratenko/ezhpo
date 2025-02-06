<?php

return [
    'Town' => [
        'title' => 'Города',
        'popupTitle' => 'города',
        'editOnField' => 'name',
        'model' => 'Town',
        'notShowHashId' => 1,
        'fields' => [
            'hash_id' => [
                'label' => 'Город',
                'type' => 'select',
                'values' => 'Town',
                'getField' => 'name',
                'concatField' => 'hash_id',
                'getFieldKey' => 'hash_id'
            ],
            'name' => [
                'label' => 'Город',
                'type' => 'text',
                'hideFilter' => 1
            ],
            'stamp_id' => [
                'label' => 'Штамп',
                'type' => 'select',
                'values' => 'Stamp',
                'getField' => 'name',
                'getFieldKey' => 'id',
                'concatField' => null,
            ],
        ],
    ],

    'Point' => [
        'title' => 'Пункты выпуска',
        'popupTitle' => 'Пункта выпуска',
        'editOnField' => 'name',
        'model' => 'Point',
        'fields' => [
            'hash_id' => [
                'label' => 'Пункт выпуска',
                'type' => 'select',
                'values' => 'Point',
                'getField' => 'name',
                'concatField' => 'hash_id',
                'getFieldKey' => 'hash_id'
            ],
            'name' => [
                'label' => 'Пункт выпуска',
                'type' => 'text',
                'hideFilter' => 1
            ],
            'pv_id' => [
                'label' => 'Город',
                'type' => 'select',
                'values' => 'Town',
                'getField' => 'name',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id'
            ],
            'company_id' => [
                'label' => 'Компания',
                'type' => 'select',
                'values' => 'Company',
                'getField' => 'name',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id',
                'noRequired' => 1,
            ],
            'stamp_id' => [
                'label' => 'Штамп',
                'type' => 'select',
                'values' => 'Stamp',
                'getField' => 'name',
                'getFieldKey' => 'id',
                'concatField' => null,
            ],
        ],
    ],

    'Req' => [
        'title' => 'Реквизиты нашей компании',
        'popupTitle' => 'Реквизитов',
        'editOnField' => 'name',
        'model' => 'Req',
        'fields' => [
            'name' => [
                'label' => 'Название',
                'type' => 'text'
            ],
            'inn' => [
                'label' => 'ИНН',
                'type' => 'number',
                'noRequired' => 1
            ],
            'bik' => [
                'label' => 'БИК',
                'type' => 'number',
                'noRequired' => 1
            ],
            'kc' => [
                'label' => 'К/С',
                'type' => 'number',
                'noRequired' => 1
            ],
            'rc' => [
                'label' => 'Р/С',
                'type' => 'number',
                'noRequired' => 1
            ],
            'banks' => [
                'label' => 'Банки',
                'type' => 'text',
                'noRequired' => 1
            ],
            'director' => [
                'label' => 'Должность руководителя',
                'type' => 'text',
                'noRequired' => 1
            ],
            'director_fio' => [
                'label' => 'ФИО Руководителя',
                'type' => 'text',
                'noRequired' => 1
            ],
            'seal' => [
                'label' => 'Печать',
                'type' => 'file',
                'noRequired' => 1
            ],
        ],
    ],

    'DDates' => [
        'title' => 'Даты контроля',
        'popupTitle' => 'Даты контроля',
        'editOnField' => 'field',
        'model' => 'DDates',
        'fields' => [
            'item_model' => [
                'label' => 'Сущность',
                'type' => 'select',
                'values' => [
                    'Driver' => 'Водитель',
                    'Car' => 'Автомобиль',
                    'Company' => 'Компания',
                ],
            ],
            'field' => [
                'label' => 'Поле даты проверки',
                'type' => 'select',
                'values' => [
                    'date_bdd' => 'Дата БДД (водитель)',
                    'date_prmo' => 'Дата ПРМО (водитель)',
                    'date_prto' => 'Дата ПРТО (автомобиль)',
                    'date_report_driver' => 'Дата снятия отчета с карты водителя (водитель)',
                    'date_techview' => 'Дата техосмотра (автомобиль)',
                    'time_skzi' => 'Срок действия СКЗИ\настройки тахографа ЕСТР (автомобиль)',
                    'time_card_driver' => 'Срок действия карты водителя (водитель)',
                    'date_osago' => 'Дата осаго (автомобиль)',
                    'date_driver_license' => 'Срок действия водительского удостоверения (водитель)',
                    'date_narcotic_test' => 'Дата тестирования на наркотики (водитель)'
                ],
            ],
            'days' => [
                'label' => 'Кол-во дней',
                'type' => 'number'
            ],
            'action' => [
                'label' => 'Действие',
                'type' => 'select',
                'values' => [
                    '+' => '+',
                    '-' => '-'
                ],
                'defaultValue' => '+',
            ],
        ],
    ],

    'Settings' => [
        'title' => 'Настройки системы',
        'popupTitle' => 'Настройки системы',
        'max' => 1,
        'editOnField' => 'id',
        'model' => 'Settings',
        'fields' => [
            'logo' => [
                'label' => 'Логотип системы',
                'type' => 'file',
                'noRequired' => 1
            ],
            'sms_api_key' => [
                'label' => 'API key sms.ru',
                'type' => 'text',
                'noRequired' => 1
            ],
            'sms_text_driver' => [
                'label' => 'Текст SMS для Водителя при непрохождении осмотра',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'sms_text_car' => [
                'label' => 'Текст SMS для Авто при непрохождении осмотра',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'sms_text_phone' => [
                'label' => 'Телефон, куда звонить в случае вопросов',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'sms_text_default' => [
                'label' => 'Текст сообщения по умолчанию',
                'type' => 'text',
                'noRequired' => 1,
            ],
        ],
    ],

    'Driver' => [
        'title' => 'Водители',
        'popupTitle' => 'Водителя',
        'otherRoles' => [
            'medic',
            'tech',
            'client'
        ],
        'editOnField' => 'fio',
        'model' => 'Driver',
        'fields' => [
            'company_id' => [
                'label' => 'Компания',
                'type' => 'select',
                'values' => 'Company',
                'getField' => 'name',
                'concatField' => 'hash_id',
                'getFieldKey' => 'id'
            ],
            'hash_id' => [
                'label' => 'ID водителя',
                'type' => 'select',
                'values' => 'Driver',
                'getField' => 'fio',
                'concatField' => 'hash_id',
                'getFieldKey' => 'hash_id'
            ],
            'fio' => [
                'label' => 'ФИО',
                'type' => 'text'
            ],
            'year_birthday' => [
                'label' => 'Дата рождения',
                'type' => 'date'
            ],
            'photo' => [
                'label' => 'Фото',
                'type' => 'file',
                'resize' => 1,
                'noRequired' => 1
            ],
            'phone' => [
                'label' => 'Телефон',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'gender' => [
                'label' => 'Пол',
                'type' => 'select',
                'values' => ['Мужской' => 'Мужской', 'Женский' => 'Женский'],
                'defaultValue' => 'Мужской',
                'noRequired' => 1,
            ],
            'group_risk' => [
                'label' => 'Группа риска',
                'type' => 'select',
                'values' => [
                    'Не указано' => 'Не указано',
                    'А\Д' => 'А\Д',
                    'Возраст' => 'Возраст',
                    'Алкоголь' => 'Алкоголь',
                    'Наркотики' => 'Наркотики',
                ],
                'defaultValue' => 'Не указано',
                'noRequired' => 1,
            ],
            'contract_id' => [
                'label' => 'Договор',
                'type' => 'select',
                'values' => 'Models\Contract',
            ],
            'note' => [
                'label' => 'Примечание',
                'type' => 'text',
                'noRequired' => 1
            ],
            'procedure_pv' => [
                'label' => 'Порядок выпуска',
                'type' => 'select',
                'values' => [
                    'Наперед без дат' => 'Наперед без дат',
                    'Наперёд с датами' => 'Наперёд с датами',
                    'Задним числом' => 'Задним числом',
                    'Фактовый' => 'Фактовый',
                ],
                'defaultValue' => 'Фактовый',
                'noRequired' => 1,
            ],
            'date_bdd' => [
                'label' => 'Дата БДД',
                'type' => 'date',
                'noRequired' => 1
            ],
            'date_prmo' => [
                'label' => 'Дата ПРМО',
                'type' => 'date',
                'noRequired' => 1
            ],
            'driver_license_issued_at' => [
                'label' => 'Дата выдачи ВУ',
                'type' => 'date',
                'noRequired' => 1
            ],
            'driver_license' => [
                'label' => 'Серия/номер ВУ',
                'type' => 'text',
                'noRequired' => 1
            ],
            'date_driver_license' => [
                'label' => 'Срок действия водительского удостоверения',
                'type' => 'date',
                'noRequired' => 1
            ],
            'snils' => [
                'label' => 'СНИЛС',
                'type' => 'text',
                'noRequired' => 1
            ],
            'date_narcotic_test' => [
                'label' => 'Дата тестирования на наркотики',
                'type' => 'date',
                'noRequired' => 1
            ],
            'date_report_driver' => [
                'label' => 'Дата снятия отчета с карты водителя',
                'type' => 'date',
                'noRequired' => 1,
            ],
            'time_card_driver' => [
                'label' => 'Срок действия карты водителя',
                'type' => 'date',
                'noRequired' => 1,
            ],
            'town_id' => [
                'label' => 'Город',
                'type' => 'select',
                'values' => 'Town',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id',
                'noRequired' => 1,
            ],
            'dismissed' => [
                'label' => 'Уволен',
                'type' => 'select',
                'values' => [
                    'Нет' => 'Нет',
                    'Да' => 'Да',
                ],
                'defaultValue' => 'Нет',
            ],
            'date_of_employment' => [
                'label' => 'Дата устройства на работу',
                'type' => 'date',
                'defaultValue' => 'current_date',
            ],
            'autosync_fields' => [
                'label' => 'Автоматическая синхронизация Полей с компанией (по умолч.)',
                'type' => 'select',
                'values' => [
                    'products_id' => 'Услуги',
                ],
                'defaultValue' => 'products_id',
                'multiple' => 1,
                'hideFilter' => 1,
            ],
            'pressure_systolic' => [
                'label' => 'Порог верхнего давления',
                'type' => 'number',
                'noRequired' => 1,
            ],
            'pressure_diastolic' => [
                'label' => 'Порог нижнего давления',
                'type' => 'number',
                'noRequired' => 1,
            ],
            'only_offline_medic_inspections' => [
                'label' => 'Блокировка прохождения МО',
                'type' => 'select',
                'values' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
                'noRequired' => 1,
            ],
        ],
    ],

    'Car' => [
        'title' => 'Автомобили',
        'popupTitle' => 'Автомобиля',
        'otherRoles' => [
            'medic',
            'tech',
            'client'
        ],
        'editOnField' => 'gos_number',
        'model' => 'Car',
        'fields' => [
            'company_id' => [
                'label' => 'Компания',
                'type' => 'select',
                'values' => 'Company',
                'getField' => 'name',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id'
            ],
            'hash_id' => [
                'label' => 'ID автомобиля',
                'type' => 'select',
                'values' => 'Car',
                'getField' => 'gos_number',
                'concatField' => 'hash_id',
                'getFieldKey' => 'hash_id'
            ],
            'gos_number' => [
                'label' => 'Гос.номер',
                'type' => 'text'
            ],
            'mark_model' => [
                'label' => 'Марка и модель',
                'type' => 'text'
            ],
            'type_auto' => [
                'label' => 'Категория Т\С',
                'type' => 'select',
                'values' => [
                    'М - мототехника (мопеды\мотоциклы\трициклы и т.п.)' => 'М - мототехника (мопеды\мотоциклы\трициклы и т.п.)',
                    'В - легковые и грузовые автомобили до 3.5 тн' => 'В - легковые и грузовые автомобили до 3.5 тн',
                    'С - грузовые т\с от 3.5 тн' => 'С - грузовые т\с от 3.5 тн',
                    'Ст - спецтранспорт' => 'Ст - спецтранспорт',
                    'D - автобусы' => 'D - автобусы',
                    'Тм - трамвай\троллейбусы' => 'Тм - трамвай\троллейбусы',
                    'Tr - трактора\с-х техника' => 'Tr - трактора\с-х техника',
                    'Е - прицепы' => 'Е - прицепы',
                ],
                'defaultValue' => 'Не установлено',
            ],
            'official_type_auto' => [
                'label' => 'Тип Т\С',
                'type' => 'text',
                'noRequired' => 1
            ],
            'trailer' => [
                'label' => 'Прицеп',
                'type' => 'select',
                'values' => [
                    'Нет' => 'Нет',
                    'Да' => 'Да'
                ],
                'defaultValue' => '',
            ],
            'contract_id' => [
                'label' => 'Договор',
                'type' => 'select',
                'values' => 'Models\Contract',
            ],
            'note' => [
                'label' => 'Примечание',
                'type' => 'text',
                'noRequired' => 1
            ],
            'procedure_pv' => [
                'label' => 'Порядок выпуска',
                'type' => 'select',
                'values' => [
                    'Наперед без дат' => 'Наперед без дат',
                    'Наперёд с датами' => 'Наперёд с датами',
                    'Задним числом' => 'Задним числом',
                    'Фактовый' => 'Фактовый',
                ],
                'defaultValue' => 'Фактовый',
                'noRequired' => 1,
            ],
            'date_prto' => [
                'label' => 'Дата ПРТО',
                'type' => 'date',
                'noRequired' => 1
            ],
            'date_techview' => [
                'label' => 'Дата техосмотра',
                'type' => 'date',
                'noRequired' => 1
            ],
            'time_skzi' => [
                'label' => 'Срок действия СКЗИ\настройки тахографа ЕСТР',
                'type' => 'date',
                'noRequired' => 1
            ],
            'date_osago' => [
                'label' => 'Дата ОСАГО',
                'type' => 'date',
                'noRequired' => 1
            ],
            'town_id' => [
                'label' => 'Город',
                'type' => 'select',
                'values' => 'Town',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id',
                'noRequired' => 1,
            ],
            'dismissed' => [
                'label' => 'Уволен',
                'type' => 'select',
                'values' => [
                    'Нет' => 'Нет',
                    'Да' => 'Да',
                ],
                'defaultValue' => 'Нет',
            ],
            'autosync_fields' => [
                'label' => 'Автоматическая синхронизация Полей с компанией (по умолч.)',
                'type' => 'select',
                'values' => [
                    'products_id' => 'Услуги',
                ],
                'defaultValue' => 'products_id',
                'multiple' => 1,
                'hideFilter' => 1,
            ],
        ],
    ],

    'Company' => [
        'title' => 'Компании',
        'popupTitle' => 'Компании',
        'editOnField' => 'name',
        'model' => 'Company',
        'fields' => [
            'hash_id' => [
                'label' => 'Название компании клиента',
                'type' => 'select',
                'filterJournalLinkKey' => 'company_id',
                'values' => 'Company',
                'getField' => 'name',
                'concatField' => 'hash_id',
                'getFieldKey' => 'hash_id'
            ],
            'official_name' => [
                'label' => 'Официальное название компании',
                'type' => 'text',
            ],
            'name' => [
                'label' => 'Название компании',
                'type' => 'text',
                'hideFilter' => 1
            ],
            'note' => [
                'label' => 'Договоренности с клиентом',
                'type' => 'text',
                'noRequired' => 1
            ],
            'comment' => [
                'label' => 'Комментарий',
                'type' => 'text',
                'noRequired' => 1
            ],
            'procedure_pv' => [
                'label' => 'Порядок выпуска',
                'type' => 'select',
                'values' => [
                    'Наперед без дат' => 'Наперед без дат',
                    'Наперёд с датами' => 'Наперёд с датами',
                    'Задним числом' => 'Задним числом',
                    'Фактовый' => 'Фактовый',
                ],
                'defaultValue' => 'Фактовый',
                'noRequired' => 1,
            ],
            'user_id' => [
                'label' => 'Ответственный',
                'type' => 'select',
                'values' => 'User',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id',
                'noRequired' => 1,
            ],
            'req_id' => [
                'label' => 'Реквизиты нашей компании',
                'type' => 'select',
                'values' => 'Req'
            ],
            'pv_id' => [
                'label' => 'ПВ',
                'type' => 'select',
                'values' => 'Point',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id',
                'noRequired' => 1
            ],
            'town_id' => [
                'label' => 'Город',
                'multiple' => 1,
                'type' => 'select',
                'values' => 'Town',
                'noRequired' => 1,
                'getFieldKey' => 'id',
                'concatField' => 'hash_id',
                'syncData' => [
                    [
                        'model' => 'Car',
                        'fieldFind' => 'company_id',
                        'text' => 'Автомобиль'
                    ],
                    [
                        'model' => 'Driver',
                        'fieldFind' => 'company_id',
                        'text' => 'Водитель'
                    ],
                ],
            ],
            'contracts' => [
                'label' => 'Договор',
                'multiple' => 1,
                'type' => 'select',
                'values' => 'Models\Service',
            ],
            'where_call' => [
                'label' => 'Номер телефона при отстранении',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'where_call_name' => [
                'label' => 'ФИО и должность кому звонить при отстранении',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'inn' => [
                'label' => 'ИНН',
                'type' => 'text'
            ],
            'kpp' => [
                'label' => 'КПП',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'ogrn' => [
                'label' => 'ОГРН',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'address' => [
                'label' => 'Адрес',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'reqs_validated' => [
                'label' => 'Корректные реквизиты',
                'type' => 'select',
                'values' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
            ],
            'one_c_synced' => [
                'label' => 'Синхронизация 1С',
                'type' => 'select',
                'values' => [
                    \App\Enums\OneCSyncStatusEnum::NON_CREATED => 'Нет',
                    \App\Enums\OneCSyncStatusEnum::NEED_UPDATE => 'Нужно обновление в 1С',
                    \App\Enums\OneCSyncStatusEnum::SYNCED => 'Да',
                ],
            ],
            'dismissed' => [
                'label' => 'Временная блокировка',
                'type' => 'select',
                'values' => [
                    'Нет' => 'Нет',
                    'Да' => 'Да',
                ],
                'defaultValue' => 'Нет',
            ],
            'has_actived_prev_month' => [
                'label' => 'Были ли активны в прошлом месяце',
                'type' => 'select',
                'values' => [
                    'Да' => 'Да',
                    'Нет' => 'Нет',
                ],
                'noRequired' => 1,
            ],
            'bitrix_link' => [
                'label' => 'Ссылка на компанию в Bitrix24',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'link_waybill' => [
                'label' => 'Ссылка на ПЛ',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'document_bdd' => [
                'label' => 'Ссылка на таблицу с документами по бдд',
                'type' => 'text',
                'noRequired' => 1,
            ],
            'pressure_systolic' => [
                'label' => 'Порог верхнего давления',
                'type' => 'number',
                'noRequired' => 1,
            ],
            'pressure_diastolic' => [
                'label' => 'Порог нижнего давления',
                'type' => 'number',
                'noRequired' => 1,
            ],
        ],
    ],

    'Product' => [
        'title' => 'Услуги',
        'popupTitle' => 'Услуги',
        'editOnField' => 'name',
        'model' => 'Product',
        'fields' => [
            'hash_id' => [
                'label' => 'Услуга',
                'type' => 'select',
                'values' => 'Product',
                'getField' => 'name',
                'getFieldKey' => 'hash_id',
                'concatField' => 'hash_id'
            ],
            'name' => [
                'label' => 'Название',
                'type' => 'text',
                'hideFilter' => 1
            ],
            'type_product' => [
                'label' => 'Тип',
                'type' => 'select',
                'values' => [
                    'Абонентская оплата' => 'Абонентская оплата',
                    'Разовые осмотры' => 'Разовые осмотры',
                    'Абонентская плата без реестров' => 'Абонентская плата без реестров',
                ],
                'defaultValue' => 'Абонентская оплата',
            ],
            'unit' => [
                'label' => 'Ед.изм.',
                'type' => 'text'
            ],
            'price_unit' => [
                'label' => 'Стоимость за единицу',
                'type' => 'number'
            ],
            'type_anketa' => [
                'label' => 'Реестр',
                'type' => 'select',
                'values' => [
                    'bdd' => 'БДД',
                    'medic' => 'Медицинский',
                    'tech' => 'Технический',
                    'pechat_pl' => 'Печать ПЛ',
                    'report_cart' => 'Отчеты с карт',
                ],
                'defaultValue' => 'Не установлено',
            ],
            'type_view' => [
                'label' => 'Тип осмотра',
                'type' => 'select',
                'values' => [
                    'Предрейсовый/Предсменный' => 'Предрейсовый/Предсменный',
                    'Послерейсовый/Послесменный' => 'Послерейсовый/Послесменный',
                    'БДД' => 'БДД',
                    'Отчёты с карт' => 'Отчёты с карт',
                    'Учет ПЛ' => 'Учет ПЛ',
                    'Печать ПЛ' => 'Печать ПЛ',
                ],
                'defaultValue' => 'Не установлено',
                'multiple' => 1,
            ],
            'essence' => [
                'label' => 'Сущности',
                'type' => 'text',
                'noRequired' => 1
            ],
        ],
    ],

    'Discount' => [
        'title' => 'Скидки',
        'popupTitle' => 'Скидка',
        'editOnField' => 'products_id',
        'model' => 'Discount',
        'fields' => [
            'products_id' => [
                'label' => 'Услуга',
                'type' => 'select',
                'values' => 'Product',
                'getField' => 'name',
                'getFieldKey' => 'id',
                'concatField' => 'hash_id'
            ],
            'trigger' => [
                'label' => 'Триггер (больше/меньше)',
                'type' => 'select',
                'values' => [
                    '>' => 'больше',
                    '<' => 'меньше',
                ],
                'defaultValue' => '>',
            ],
            'porog' => [
                'label' => 'Пороговое значение',
                'type' => 'number'
            ],
            'discount' => [
                'label' => 'Скидка (%)',
                'type' => 'porog'
            ],
        ],
    ],

    'Instr' => [
        'title' => 'Виды инструктажей',
        'popupTitle' => 'Инструктажа',
        'editOnField' => 'name',
        'model' => 'Instr',
        'fields' => [
            'photos' => [
                'label' => 'Фото',
                'type' => 'file',
                'noRequired' => 1,
                'hideFilter' => 1
            ],
            'hash_id' => [
                'label' => 'Инструктаж',
                'type' => 'select',
                'values' => 'Instr',
                'getField' => 'name',
                'concatField' => 'hash_id',
                'getFieldKey' => 'hash_id'
            ],
            'name' => [
                'label' => 'Название',
                'type' => 'text',
                'hideFilter' => 1
            ],
            'descr' => [
                'label' => 'Описание',
                'type' => 'text'
            ],
            'type_briefing' => [
                'label' => 'Вид инструктажа',
                'type' => 'select',
                'values' => [
                    'Вводный' => 'Вводный',
                    'Предрейсовый' => 'Предрейсовый',
                    'Сезонный (осенне-зимний)' => 'Сезонный (осенне-зимний)',
                    'Сезонный (весенне-летний)' => 'Сезонный (весенне-летний)',
                    'Специальный' => 'Специальный',
                ],
                'defaultValue' => 'Вводный',
            ],
            'youtube' => [
                'label' => 'Ссылка на YouTube\RUTUBE',
                'type' => 'text'
            ],
            'active' => [
                'label' => 'Активен',
                'type' => 'select',
                'values' => [
                    0 => 'Нет',
                    1 => 'Да',
                ],
                'defaultValue' => 'Да',
            ],
            'sort' => [
                'label' => 'Сортировка',
                'type' => 'number',
                'noRequired' => 1,
                'hideFilter' => 1
            ],
            'signature' => [
                'label' => 'ЭЛ подпись водителя',
                'type' => 'number',
                'noRequired' => 1
            ],
        ],
    ],
];
