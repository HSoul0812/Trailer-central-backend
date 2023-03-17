<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ADF Import
    |--------------------------------------------------------------------------
    |
    | Here are the ADF Import details.
    |
    */
    'imports' => [
        'defaults' => [
            'email' => env('ADF_IMPORT_EMAIL', 'adf@operatebeyond.com'),
            'password' => env('ADF_IMPORT_PASSWORD', ''),
            'inbox' => env('ADF_IMPORT_INBOX', 'INBOX'),
            'invalid' => env('ADF_IMPORT_INVALID', 'Invalid'),
            'processed' => env('ADF_IMPORT_PROCESSED', 'Processed'),
            'driver' => env('ADF_IMPORT_DRIVER', 'smtp'),
            'host' => env('ADF_IMPORT_HOST', 'operatebeyond.com'),
            'ssl' => env('ADF_IMPORT_SSL', 'tls'),
            'port' => env('ADF_IMPORT_PORT', 587)
        ],
        'gmail' => [
            'email' => env('ADF_IMPORT_EMAIL', 'adf@operatebeyond.com'),
            'domain' => env('ADF_IMPORT_DOMAIN', 'operatebeyond.com'),
            'inbox' => env('ADF_IMPORT_INBOX', 'INBOX'),
            'invalid' => env('ADF_IMPORT_INVALID', 'Invalid'),
            'unmapped' => env('ADF_IMPORT_UNMAPPED', 'Unmapped'),
            'processed' => env('ADF_IMPORT_PROCESSED', 'Processed'),
            'move' => env('ADF_IMPORT_MOVE_LABEL', (env('APP_ENV') === 'production'))
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | ADF Export
    |--------------------------------------------------------------------------
    |
    | Here are the ADF Export details.
    |
    */
    'exports' => [
        'copied_emails' => env('COPIED_EMAILS', 'alberto@trailercentral.com,adfexports@operatebeyond.com')
    ]
];
