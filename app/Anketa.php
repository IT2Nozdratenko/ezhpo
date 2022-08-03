<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anketa extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'hash_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'hash_id');
    }


    public $fillable
        = [
            'id',
            'type_anketa',

            'car_id',
            'car_gos_number',
            'car_mark_model',

            'driver_id',
            'driver_fio',
            'driver_group_risk',

            'user_id',
            'user_eds',
            'user_name',

            'company_id',
            'company_name',

            'pv_id',
            'date',
            'created_at',
            'number_list_road', //'date_number_list_road',
            'type_view',
            'tonometer',
            'signature',
            't_people',
            'proba_alko',
            'test_narko',
            'med_view',
            'odometer',
            'point_reys_control',
            'admitted',

            // Новые поля
            'driver_gender',
            'driver_year_birthday',
            'complaint',
            'condition_visible_sliz',
            'condition_koj_pokr',
            'is_dop',
            'result_dop',

            // Журнал печати ПЛ
            'date_pechat_pl',
            'count_pl',
            'period_pl',
            'added_to_dop',
            'added_to_mo',
            'realy',

            // Журнал БДД
            'type_briefing',

            // ПАК
            'pulse',
            'alcometer_mode',
            'alcometer_result',
            'type_trip',
            'questions',
            'photos',
            'videos',

            //системные поля
            'in_cart',
            'is_pak',
            'protokol_path',
            'comments',
            'flag_pak',
            'connected_hash',
        ];

    public static $anketsKeys
        = [
            'Dop'         => 'Журнал учёта путевых листов',
            'medic'       => 'Журнал МО',
            'tech'        => 'Журнал ТО',
            'bdd'         => 'Журнал инструктажей по БДД',
            'report_cart' => 'Реестр снятия отчетов с карт',
            'pechat_pl'   => 'Журнал печати ПЛ',
            //'vid_pl' => 'Реестр выданных путевых листов',
            'pak'         => 'Журнал СДПО',
            'pak_queue'   => 'Очередь на утверждение',
        ];

    public static $fieldsGroupFirst
        = [ // Группа 1 (показываем сразу в HOME)
            'medic' => [
                'date'         => 'Дата осмотра',
                'company_id'   => 'Компания',
                'period_pl'    => 'Период выдачи ПЛ',
                'driver_fio'   => 'Водитель',
                'pv_id'        => 'Пункт выпуска',
                'realy'        => 'Осмотр реальный?',
                'type_view'    => 'Тип осмотра',
                'proba_alko'   => 'Признаки опьянения', // Проба на алкоголь
                'test_narko'   => 'Тест на наркотики',
            ],

            'pak' => [
                'date'           => 'Дата осмотра',
                'company_id'   => 'Место работы',
                'driver_fio'     => 'Водитель',
                'pv_id'          => 'Пункт выпуска',
                'car_mark_model' => 'Автомобиль',
                'flag_pak'       => 'Флаг СДПО',
            ],

            'pak_queue' => [
                'created_at' => 'Дата создания',
                'driver_fio' => 'Водитель',
                'pv_id'      => 'Пункт выпуска',
                'tonometer'  => "Артериальное давление",
                't_people'   => 'Температура тела',
                'proba_alko' => 'Признаки опьянения', // Проба на алкоголь
                'complaint'  => 'Жалобы',
                'admitted'   => 'Заключение о результатах осмотра',
                'photos'     => 'Фото',
                'videos'     => 'Видео',

            ],

            'tech' => [
                'date'           => 'Дата осмотра',
                'car_gos_number' => 'Гос. регистрационный номер ТС',
                'realy'        => 'Осмотр реальный?',
                'car_type_auto' => 'Категория ТС',
                'company_id'   => 'Компания',
                'type_view'          => 'Тип осмотра',
            ],

            'Dop' => [
                'date'           => 'Дата, время проведения осмотра',
                'car_mark_model' => 'Автомобиль',
                'company_id'   => 'Компания',
                'driver_fio'     => 'Водитель',

                // Доп поля
                'pv_id'          => 'Пункт выпуска',
            ],

            'bdd' => [
                'company_id'  => 'Компания',
                'date'          => 'Дата инструктажа',
                'driver_fio'   => 'Ф.И.О водителя',
            ],

            'report_cart' => [
                'date'         => 'Дата снятия отчета',
                'driver_fio'   => 'Ф.И.О водителя',
                'company_id' => 'Компания',
            ],

            'pechat_pl' => [
                'date'         => 'Дата выдачи',
                'driver_fio'   => 'ФИО водителя',
                'company_name' => 'Компания',
            ],

            /*'vid_pl' => [
                'date' => 'Дата выдачи ПЛ',
                'company_name' => 'Компания',
                'count_pl' => 'Количество выданных ПЛ',
                'user_name' => 'Ф.И.О сотрудника, который выдал ПЛ',
                'pv_id' => 'Пункт выпуска',
                'added_to_dop' => 'Внесено в журнал ТО',
                'added_to_mo' => 'Внесено в журнал МО',
                'period_pl' => 'Комментарий',
                'car_gos_number' => 'Госномер автомобиля'
            ]*/
        ];

    public static $blockedToExportFields
        = [
            'medic' => [
                // Поля не в выгрузку
                'created_at'        => 'Дата создания',
                'driver_group_risk' => 'Группа риска',
                'company_id'        => 'ID компании',
                'driver_id'         => 'ID водителя',
                'photos'            => 'Фото',
                'med_view'          => 'Мед показания',
                'pv_id'             => 'Пункт выпуска',
                'car_mark_model'    => 'Автомобиль',
                'car_id'            => 'ID автомобиля',
                'number_list_road'  => 'Номер путевого листа',
                'type_view'         => 'Тип осмотра',
                'is_pak'            => 'ПАК',
                'flag_pak'          => 'Флаг СДПО',
                'company_name'      => 'Место работы',
                'realy'             => 'Осмотр реальный?',
                'videos'            => 'Видео',
                'is_dop'            => 'Режим ввода ПЛ',
                'result_dop'        => 'Результат ввода ПЛ',
                'period_pl'         => 'Период ПЛ',
                'signature'         => 'ЭЛ подпись водителя',
            ],
            'tech'  => [
                'realy'        => 'Осмотр реальный?',
            ],
        ];

    public static $fieldsKeysTable = [
        'medic' => [
            'date',
            'driver_fio',
            'period_pl',
            'created_at',
            'driver_group_risk',
            'type_view',
            'realy',
            'proba_alko', // Проба на алкоголь
            'test_narko',
            'company_name',
            'company_id',
            'pv_id',
            'driver_id',
            'user_name',
            'driver_gender',
            'driver_year_birthday',
            'complaint',
            'condition_visible_sliz',
            'condition_koj_pokr',
            't_people',
            'tonometer',
            'pulse',
            'admitted',
            'user_eds',
            'photos',
            'videos',
            'med_view',
            'flag_pak',
            'is_dop',
        ],
        'tech' => [
            'date',
            'car_gos_number',
            'period_pl',
            'created_at',
            'car_type_auto',
            'type_view',
            'realy',
            'driver_fio',
            'company_name',
            'company_id',
            'pv_id',
            'car_id',
            'odometer',
            'driver_id',
            'number_list_road',
            'point_reys_control',
            'user_eds',
            'is_dop',
            'user_name',
        ],
        'Dop' => [
            'date',
            'created_at',
            'driver_fio',
            'company_name',
            'company_id',
            'pv_id',
            'number_list_road',
            'car_gos_number',
            'car_mark_model',
            'driver_id',
            'car_id',
            'user_eds',
            'user_name',
        ],
        'bdd' => [
            'date',
            'driver_fio',
            'type_briefing',
            'company_name',
            'created_at',
            'user_name',
            'pv_id',
            'user_eds',
            'driver_id',
            'signature',
        ],
        'report_cart' => [
            'date',
            'driver_fio',
            'company_name',
            'user_name',
            'created_at',
            'company_id',
            'pv_id',
            'user_eds',
            'driver_id',
            'signature',
        ],
        'pechat_pl' => [
            'date',
            'driver_fio',
            'count_pl',
            'company_name',
            'user_name',
            'pv_id',
            'created_at',
            'company_id',
            'user_eds',
        ]
    ];

    public static $fieldsKeys
        = [ // Группа 2 (скрыты по умолчанию)
            'medic_export_pl' => [
                'date' => 'Дата и время осмотра',
                'driver_fio' => 'ФИО РАБОТНИКА',
                'driver_gender' => 'ПОЛ',
                'driver_year_birthday' => 'ДАТА РОЖДЕНИЯ',
                'complaint' => 'ЖАЛОБЫ',
                'condition_visible_sliz' => 'СОСТОЯНИЕ ВИДИМЫХ СЛИЗИСТЫХ',
                'condition_koj_pokr' => 'СОСТОЯНИЕ КОЖНЫХ ПОКРОВОВ',
                't_people' => 'ТЕМПЕРАТУРА ТЕЛА',
                'tonometer' => 'АРТЕРИАЛЬНОЕ ДАВЛЕНИЯ',
                'pulse' => 'ПУЛЬС',
                'proba_alko' => 'ПРИЗНАКИ ОПЬЯНЕНИЯ',
                'test_narko' => 'Тест на наркотики',
                'admitted' => 'ЗАКЛЮЧЕНИЕ О РЕЗУЛЬТАТАХ ОСМОТРА',
                'user_name' => 'ФИО медицинского раблотника',
                'user_eds' => 'ЭЦП МЕДИЦИНСКОГО РАБОТНИКА ',
            ],

            'tech_export_to' => [
                'date'               => 'Дата, время проведения контроля',
                'car_mark_model'     => 'Наименование марки, модели ТС',
                'car_gos_number' => 'Гос. регистрационный номер ТС',
                'driver_fio'         => 'ФИО водителя',
                'odometer'           => 'Показания одометра  (полные километры пробега при проведении контроля)',
                'point_reys_control' => 'Отметка о прохождении контроля',
                'user_name'          => 'ФИО лица, проводившего контроль',
                'user_eds'           => 'Подпись лица, проводившего контроль (ЭЦП)',
            ],
            'tech_export_pl' => [
                'number_list_road' => 'Номер ПЛ',
                'date'             => 'Дата и время выдачи ПЛ',
                'car_mark_model'   => 'Наименование марки, модели транспортного средства',
                'car_gos_number'   => 'Гос. регистрационный номер ТС',
                'driver_fio'       => 'ФИО водителя',
                'user_name'        => 'ФИО лица, выдавшего ПЛ',
                'user_eds'         => 'Подпись лица, выдавшего ПЛ (ЭЦП)',
            ],

            'bdd_export_prikaz' => [
                'date'          => 'Дата инструктажа',
                'type_briefing' => 'Вид инструктажа',
                'driver_fio'    => 'ФИО водителя',
                'company_name'  => 'Компания',
                'user_name'     => 'ФИО лица, проводившего инструктаж',
                'user_id'       => 'Должность лица, проводившего инструктаж',    // Ну этой хуйни в таблице нет
                'signature'     => 'Подпись водителя, прошедшего инструктаж',
                'user_eds'      => 'Подпись лица, проводившего инструктаж (ЭЦП)',
            ],

            'dop_export_pl' => [
                'number_list_road' => 'Номер ПЛ',
                'date' => 'Дата и время выдачи ПЛ',
                'car_mark_model' => 'Наименование марки, модели транспортного средства',
                'car_gos_number' => 'Государственный регистрационный номер транспортного средства',
                'driver_fio' => 'ФИО водителя',
                'user_name' => 'ФИО лица, выдавшего ПЛ',
                'user_eds' => 'Подпись лица, выдавшего ПЛ (ЭЦП)'
            ],


            'medic' => [
                'company_name'             => 'Компания',
                'company_id'             => 'ID Компании',
                'date'                   => 'Дата и время осмотра',
                'period_pl'              => 'Период выдачи ПЛ',
                'driver_fio'             => 'ФИО работника',
                'realy'                  => 'Осмотр реальный?',
                'type_view'              => 'Тип осмотра',
                'proba_alko'             => 'Признаки опьянения', // Проба на алкоголь
                'test_narko'             => 'Тест на наркотики',
                'driver_group_risk'      => 'Группа риска',
                'driver_gender'          => 'Пол',
                'driver_year_birthday'   => 'Дата рождения',
                'complaint'              => 'Жалобы',
                'condition_visible_sliz' => 'Состояние видимых слизистых',
                'condition_koj_pokr'     => 'Состояние кожных покровов',
                't_people'               => 'Температура тела',
                'tonometer'              => "Артериальное давление",
                'pulse'                  => 'Пульс',
                'test_narko'             => 'Тест на наркотики',
                'admitted'               => 'Заключение о результатах осмотра',
                'user_name'              => 'ФИО ответственного',
                'user_eds'               => 'ЭЦП медицинского работника',

                // Поля не в выгрузку
                'created_at'             => 'Дата создания',
                'driver_id'              => 'ID водителя',
                'photos'                 => 'Фото',
                'videos'                 => 'Видео',
                'med_view'               => 'Мед показания',
                'pv_id'                  => 'Пункт выпуска',
                //'car_mark_model' => 'Автомобиль',
                //'car_id' => 'ID автомобиля',
                //'number_list_road' => 'Номер путевого листа',
                'flag_pak'               => 'Флаг СДПО',
                'is_dop'                 => 'Режим ввода ПЛ',
            ],

            'pak' => [
                'date'                   => 'Дата и время осмотра',
                'user_name'              => 'ФИО работника',
                'driver_gender'          => 'Пол',
                'driver_year_birthday'   => 'Дата рождения',
                'complaint'              => 'Жалобы',
                'condition_visible_sliz' => 'Состояние видимых слизистых',
                'condition_koj_pokr'     => 'Состояние кожных покровов',
                't_people'               => 'Температура тела',
                'tonometer'              => "Артериальное давление",
                'pulse'                  => 'Пульс',
                'proba_alko'             => 'Признаки опьянения', // Проба на алкоголь
                'admitted'               => 'Заключение о результатах осмотра',
                'user_eds'               => 'ЭЦП медицинского работника',

                // Поля не в выгрузку
                'created_at'             => 'Дата создания',
                'driver_group_risk'      => 'Группа риска',
                'driver_fio'             => 'Водитель',
                'company_id'             => 'ID компании',
                'driver_id'              => 'ID водителя',
                'photos'                 => 'Фото',
                'med_view'               => 'Мед показания',
                'pv_id'                  => 'Пункт выпуска',
                'car_mark_model'         => 'Автомобиль',
                'car_id'                 => 'ID автомобиля',
                'number_list_road'       => 'Номер путевого листа',
                //'date_number_list_road' => 'Срок действия путевого листа',
                'type_view'              => 'Тип осмотра',
                'comments'               => 'Комментарий',
                'flag_pak'               => 'Флаг СДПО',
            ],

            'pak_queue' => [
                'created_at' => 'Дата создания',
                'driver_fio' => 'Водитель',
                'pv_id'      => 'Пункт выпуска',
                'tonometer'  => "Артериальное давление",
                't_people'   => 'Температура тела',
                'proba_alko' => 'Признаки опьянения', // Проба на алкоголь
                'complaint'  => 'Жалобы',
                'admitted'   => 'Заключение о результатах осмотра',
                'photos'     => 'Фото',
                'videos'     => 'Видео',
            ],

            /**
             * Технический осмотр (параметры полей)
             */
            'tech'      => [
                'company_id'     => 'ID Компании',
                'company_name'     => 'Компания',
                'date'           => 'Дата, время проведения контроля',
                'period_pl'      => 'Период выдачи ПЛ',
                'created_at'     => 'Дата создания',
                'car_gos_number' => 'Гос.регистрационный номер ТС',
                'realy'        => 'Осмотр реальный?',
                'car_type_auto' => 'Категория ТС',
                'car_mark_model' => 'Марка автомобиля',
                'type_view'          => 'Тип осмотра',
                'driver_fio'     => 'ФИО Водителя',

                // ID'шники
                'driver_id'      => 'ID водителя',
                'car_id'         => 'ID автомобиля',

                // Доп поля

                'number_list_road'   => 'Номер ПЛ',
                //'date_number_list_road' => 'Срок действия путевого листа',
                'odometer'           => 'показания одометра',
                'point_reys_control' => 'Отметка о прохождении контроля',
                'user_name'          => 'ФИО ответственного',
                'user_eds'           => 'Подпись лица, проводившего контроль',
                'pv_id'              => 'Пункт выпуска',
                'is_dop'             => 'Режим ввода ПЛ',
            ],

            /**
             * Дополнительные осмотр (параметры полей)
             */
            'Dop'       => [
                'date'           => 'Дата и время выдачи пл',
                'company_name'   => 'Компания',
                'driver_fio'     => 'ФИО водителя',
                'car_mark_model' => 'Марка автомобиля',
                'car_gos_number' => 'Государственный регистрационный номер транспортного средства',

                // ID'шники
                'company_id'     => 'ID компании',
                'driver_id'      => 'ID водителя',
                'car_id'         => 'ID автомобиля',

                // Доп поля

                'number_list_road' => 'Номер путевого листа',
                //'date_number_list_road' => 'Срок действия путевого листа',
                'pv_id'            => 'Пункт выпуска',
                'user_name'        => 'ФИО ответственного',
                'user_eds'         => 'ЭЦП контролера',
                'created_at'       => 'Дата/Время создания записи',
            ],

            'Dop_prikaz'  => [
                'number_list_road' => 'Номер ПЛ',
                'date'             => 'Дата и время выдачи ПЛ',
                'car_mark_model'   => 'Наименование марки, модели транспортного средства',
                'car_gos_number' => 'Гос. регистрационный номер ТС',
                'driver_fio'       => 'ФИО водителя',
                'user_name'        => 'ФИО лица, выдавшего ПЛ',
                'user_eds'         => 'Подпись лица, выдавшего ПЛ (ЭЦП)',
            ],

            /**
             * ЖУРНАЛЫ
             */
            'report_cart' => [
                'company_id'   => 'ID Компании',
                'company_name'   => 'Компания',
                'date'         => 'Дата снятия отчета',
                'driver_fio'   => 'Ф.И.О водителя',
                'user_name'    => 'Ф.И.О (при наличии) лица, проводившего снятие',
                'user_eds'     => 'Подпись лица, проводившего снятие',
                'pv_id'        => 'Пункт выпуска',
                'driver_id'    => 'ID водителя',
                'signature'    => 'ЭЛ подпись водителя',
                'created_at' => 'Дата/Время создания записи',
            ],

            'pechat_pl' => [
                'company_name' => 'Компания',
                'company_id' => 'ID Компания',
                'date'         => 'Дата распечатки ПЛ',
                'driver_fio'   => 'ФИО водителя',
                'count_pl'     => 'Количество распечатанных ПЛ',
                'user_name'    => 'Ф.И.О сотрудника, который готовил ПЛ',
                'user_eds'     => 'ЭЦП сотрудника',
                'pv_id'        => 'Пункт выпуска',
            ],

            /*'vid_pl' => [
                'date' => 'Дата выдачи ПЛ',
                'company_name' => 'Компания',
                'count_pl' => 'Количество выданных ПЛ',
                'user_name' => 'Ф.И.О сотрудника, который выдал ПЛ',
                'user_eds' => 'ЭЦП сотрудника',
                'pv_id' => 'Пункт выпуска',
                'driver_id' => 'ID водителя',
                'driver_fio' => 'Ф.И.О водителя',
                'car_gos_number' => 'Государственный регистрационный номер транспортного средства',
                'car_id' => 'ID автомобиля',
                'added_to_dop' => 'Внесено в журнал ТО',
                'added_to_mo' => 'Внесено в журнал МО',
                'period_pl' => 'Комментарий'
            ],*/

            'bdd' => [
                'company_name'    => 'Компания',
                'company_id'    => 'ID Компании',
                'date'          => 'Дата, время',
                'created_at'    => 'Дата внесения в журнал',
                'type_briefing' => 'Вид инструктажа',
                'driver_fio'    => 'Ф.И.О водителя, прошедшего инструктаж',
                'user_name'     => 'Ф.И.О (при наличии) лица, проводившего инструктаж',
                'pv_id'         => 'Пункт выпуска',
                'user_eds'      => 'Подпись лица, проводившего инструктаж',
                'driver_id'     => 'ID водителя',
                'signature'     => 'ЭЛ подпись водителя',
            ],

        ];


    public static function getAll()
    {
        return self::all();
    }
}
