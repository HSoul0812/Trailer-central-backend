<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Twilio API
    |--------------------------------------------------------------------------
    |
    | Here are the Twilio API keys.
    |
    */

    // Twilio Account SID
    'sid'   => env('TWILIO_ACCOUNT_ID', ''),

    // Twilio Auth Token
    'token' => env('TWILIO_AUTH_TOKEN', ''),

    // API Key Settings
    'api'   => [
        'key'    => env('TWILIO_API_KEY', ''),
        'secret' => env('TWILIO_API_SECRET', ''),
    ]
];