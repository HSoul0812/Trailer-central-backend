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
            'folder' => env('ADF_IMPORT_FOLDER', 'INBOX')
        ]
    ]
];
