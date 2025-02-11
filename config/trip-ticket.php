<?php

return [
    'print' => [
        'template' => storage_path('app/templates/trip-tickets/print.xlsx'),
        '4s' => [
            'template' => [
                'front' => [
                    'sheet' => '4s',
                    'prefix' => '4-С'
                ],
                'reverse' => [
                    'sheet' => '4s-reverse',
                    'prefix' => '4-С обр'
                ],
            ],
            'stamps' => [
                'medic' => [
                    'reqName' => 'ООО "Трансавто-7"',
                    'license' => 'Бессрочная лицензия от 09.12.2020 № Л041-1177-91/00366739',
                    'comment' => <<<TEXT
                    ПРОШЕЛ ПРЕДРЕЙСОВЫЙ МЕДИЦИНСКИЙ
                    ОСМОТР. К ИСПОЛНЕНИЮ ТРУДОВЫХ
                    ОБЯЗАННОСТЕЙ ДОПУЩЕН
                    TEXT
                ],
                'tech' => 'ВЫПУСК НА ЛИНИЮ РАЗРЕШЕН',
            ],
        ],
    ],
];
