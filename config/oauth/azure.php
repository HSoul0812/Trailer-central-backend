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
        'id'     => env('MS_AZURE_CLIENT_ID', ''),
        'secret' => env('MS_AZURE_CLIENT_SECRET', ''),
    ],

    // Redirect URI
    'redirectUri'     => env('MS_AZURE_REDIRECT_URI', 'https://crm.trailercentral.com/oauth-result/office365'),

    // Required Scopes
    'scopes'          => env('MS_AZURE_SCOPES', 'openid profile offline_access IMAP.AccessAsUser.All SMTP.Send offline_access email'),

    // Authority Settings
    'authority'       => [
        'root'      => env('MS_AZURE_AUTHORITY', 'https://login.microsoftonline.com/common'),
        'authorize' => env('MS_AZURE_AUTHORIZE', '/oauth2/v2.0/authorize'),
        'token'     => env('MS_AZURE_TOKEN', '/oauth2/v2.0/token'),
    ]
];