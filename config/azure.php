<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Microsoft Azure
    |--------------------------------------------------------------------------
    |
    | Here are the MS Azure Graph details.
    |
    */

    // App Key Settings
    'app'             => [
        'id'     => env('OAUTH_APP_ID', ''),
        'secret' => env('OAUTH_APP_SECRET', ''),
    ],

    // Redirect URI
    'redirectUri'     => env('OAUTH_REDIRECT_URI', 'https://crm.trailercentral.com/oauth-result/office365'),

    // Required Scopes
    'scopes'          => env('OAUTH_SCOPES', 'openid profile offline_access IMAP.AccessAsUser.All SMTP.Send offline_access email'),

    // Authority Settings
    'authority'       => [
        'root'      => env('OAUTH_AUTHORITY', 'https://login.microsoftonline.com/common'),
        'authorize' => env('OAUTH_AUTHORIZE_ENDPOINT', '/oauth2/v2.0/authorize'),
        'token'     => env('OAUTH_TOKEN_ENDPOINT', '/oauth2/v2.0/token'),
    ]
];