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

            'emails' => [
                'failed_bot_ips_fetch' => [
                    'send_mail' => env('TT_MIDDLEWARES_HUMAN_ONLY_EMAILS_FAILED_BOT_IPS_FETCH_SEND_MAIL', false),
                    'mail_to' => [
                        'pond@trailercentral.com',
                        'francois@trailercentral.com',
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain configs
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

    /*
    |--------------------------------------------------------------------------
    | Report configs
    |--------------------------------------------------------------------------
    |
    | This config keep all the settings related to each report
    |
    */

    'report' => [
        'inventory-view-and-impression' => [
            'send_mail' => env('TT_REPORT_INVENTORY_VIEW_AND_IMPRESSION_SEND_MAIL', false),
            'mail_to' => [
                'francois@trailercentral.com',
            ],
        ],
    ],
];
