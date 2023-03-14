<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Middleware specific configs
    |--------------------------------------------------------------------------
    |
    | Use this setting to set middleware specific config values
    | Example values: 127.0.0.1,145.3.1.0,190.1.1.5,64.23.45.67
    |
    */

    'middlewares' => [
        'human_only' => [
            'allow_ips' => env('TT_MIDDLEWARES_HUMAN_ONLY_ALLOW_IPS', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain  configs
    |--------------------------------------------------------------------------
    |
    | We can use this array to see which domain are hosting frontend and backend
    | code
    |
    */

    'domains' => [
        'frontend' => [
            'localhost',
            'qa.trailertrader.com',
            'deployment.trailertrader.com',
            'trailertrader.com',
        ],
        'backend' => [
            'localhost',
            'https://trailertrader-staging.trailercentral.com',
            'https://backend.trailertrader.com'
        ],
    ],
];
