<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google API
    |--------------------------------------------------------------------------
    |
    | Here are the Google API details.
    |
    */

    // API Key Settings
    'api'             => [
        'key'    => env('GOOGLE_OAUTH_API_KEY', ''),
        'secret' => env('GOOGLE_OAUTH_API_SECRET', ''),
    ],

    // App Key Settings
    'app'             => [
        'id'     => env('GOOGLE_OAUTH_CLIENT_ID', ''),
        'secret' => env('GOOGLE_OAUTH_CLIENT_SECRET', ''),
        'name'   => env('GOOGLE_OAUTH_APP_NAME', 'Operate Beyond CRM')
    ],

    // Redirect URI
    'redirectUri'     => env('GOOGLE_REDIRECT_URI', 'https://crm.trailercentral.com/oauth-result/google'),

    // Required Scopes
    'scopes'          => env('GOOGLE_OAUTH_SCOPES', 'openid profile offline_access email https://www.googleapis.com/auth/gmail.send https://www.googleapis.com/auth/gmail.readonly'),
    'discovery'       => env('GOOGLE_OAUTH_DISCOVERY', 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest')
];