<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Email
    |--------------------------------------------------------------------------
    |
    | Here are the System Email details.
    |
    */

    // API Key Settings
    'api'             => [
        'key'    => env('SYSTEM_EMAIL_API_KEY', ''),
        'secret' => env('SYSTEM_EMAIL_API_SECRET', ''),
    ],

    // App Key Settings
    'app'             => [
        'id'     => env('SYSTEM_EMAIL_CLIENT_ID', ''),
        'secret' => env('SYSTEM_EMAIL_CLIENT_SECRET', ''),
        'name'   => env('SYSTEM_EMAIL_APP_NAME', 'Operate Beyond CRM')
    ],

    // Redirect URI
    'redirectUri'     => env('SYSTEM_EMAIL_REDIRECT_URI', 'https://api.v1.trailercentral.com/oauth-result/google'),

    // Required Scopes
    'scopes'          => env('SYSTEM_EMAIL_SCOPES', 'openid profile offline_access email https://mail.google.com/'),
    'discovery'       => env('SYSTEM_EMAIL_DISCOVERY', 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest')
];