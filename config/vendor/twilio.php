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
    ],

    // Demo Numbers
    'numbers' => [
        'from' => explode(",", env('TWILIO_NUMBERS_FROM', '')),
        'to' => explode(",", env('TWILIO_NUMBERS_TO', ''))
    ],

    // Reply URL
    'reply' => env('TWILIO_REPLY_ENDPOINT', 'https://crm.trailercentral.com/twilio/reply-twilio-message'),

    // Verify URL
    'verify' => env('TWILIO_VERIFY_ENDPOINT', env('APP_URL') . '/webhooks/twilio/reply')
];