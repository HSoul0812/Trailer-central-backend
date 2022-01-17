<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'here' => [
        'key' => env('HERE_API_KEY', ''),
    ],

    'trailercentral' => [
        'api'          => env('TC_API_URL', 'https://api.v1.staging.trailercentral.com/api/'),
        'access_token' => env('TC_API_ACCESS_TOKEN', 'f3c74ad00f954cc698face16ff78d791'),
    ],

    'tomtom' => [
        'key' => env('TOMTOM_API_KEY', ''),
    ],
];
