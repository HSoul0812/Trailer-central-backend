<?php

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
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
        'token' => env('SES_TOKEN'),
        'options' => [
            'ConfigurationSetName' => env('SES_CONFIG_SET'),
        ],
        'from' => [
            'address' => env('SES_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'noreply@operatebeyond.com')),
            'name' => env('SES_FROM_NAME', env('MAIL_FROM_NAME', 'OperateBeyond')),
        ]
    ],

    'stripe' => [
        'public_key' => env('STRIPE_KEY'),
        'secret_key' => env('STRIPE_SECRET')
    ],

    'aws' => [
        'url' => env('AWS_URL'),
        'app_key' => env('APP_KEY'),
        'bucket' => env('AWS_BUCKET'),
        'access_key' => env('AWS_ACCESS_KEY_ID'),
        'secret_key' => env('AWS_SECRET_ACCESS_KEY'),
        'default_region' => env('AWS_DEFAULT_REGION'),
        'prod_url' => env('AWS_PROD_URL', 'https://dealer-cdn.com')
    ]

];
