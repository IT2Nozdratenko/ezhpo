<?php

return [
    'fill-day-hash' => filter_var(env('RUN_FILL_DAY_HASH_FORMS_COMMAND', false), FILTER_VALIDATE_BOOLEAN),
    'fill-day-hash-chunk-size' => env('FILL_DAY_HASH_COMMAND_CHUNK', 50000),
    'fix' => filter_var(env('RUN_FIX_FORMS_COMMAND', false), FILTER_VALIDATE_BOOLEAN),
    'fix-chunk-size' => env('FIX_FORMS_COMMAND_CHUNK', 30000),
    'transfer' => filter_var(env('RUN_TRANSFER_FORMS_COMMAND', false), FILTER_VALIDATE_BOOLEAN),
    'transfer-chunk-size' => env('TRANSFER_FORMS_COMMAND_CHUNK', 15000),
    'restore-foreign' => filter_var(env('RUN_RESTORE_FOREIGN_FORMS_COMMAND', false), FILTER_VALIDATE_BOOLEAN),
    'restore-foreign-chunk-size' => env('RESTORE_FOREIGN_FORMS_COMMAND_CHUNK', 15000),

    'verification' => [
        'phone' => env('FORM_VERIFICATION_PHONE', ''),
    ],
    'labeling' => [
        'template' => env('FORM_LABELING_TEMPLATE', ''),
    ],

    'deleting-mo-files' => filter_var(env('RUN_DELETING_MO_FILES', false), FILTER_VALIDATE_BOOLEAN),
    'days-before-deleting-mo-files' => env('DAYS_BEFORE_DELETING_MO_FILES', 45),
    'deleting-mo-files-chunk' => env('DELETING_MO_FILES_CHUNK', 50000),
];
