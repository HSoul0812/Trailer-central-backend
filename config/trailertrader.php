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
];
