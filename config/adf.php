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
            'email' => env('ADF_IMPORT_EMAIL', 'adf@trailercentral.com'),
            'password' => env('ADF_IMPORT_PASSWORD', ''),
            'folder' => env('ADF_IMPORT_FOLDER', 'INBOX'),
            'driver' => env('ADF_IMPORT_DRIVER', 'smtp'),
            'host' => env('ADF_IMPORT_HOST', 'trailercentral.com'),
            'ssl' => env('ADF_IMPORT_SSL', 'tls'),
            'port' => env('ADF_IMPORT_PORT', 587)
        ],
        'gmail' => [
            'email' => env('ADF_IMPORT_EMAIL', 'adf@trailercentral.com'),
            'folder' => env('ADF_IMPORT_FOLDER', 'INBOX'),
            'access_token' => env('ADF_IMPORT_ACCESS_TOKEN', ''),
            'id_token' => env('ADF_IMPORT_ID_TOKEN', ''),
            'refresh_token' => env('ADF_IMPORT_REFRESH_TOKEN', ''),
            'expires_in' => env('ADF_IMPORT_EXPIRES_IN', '3599'),
            'issued_at' => env('ADF_IMPORT_ISSUED_AT', date('Y-m-d H:i:s')),
            'scope' => env('ADF_IMPORT_SCOPES', 'https://mail.google.com/')
        ]
    ]
];
