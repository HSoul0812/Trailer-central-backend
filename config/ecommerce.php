<?php

return [
    'allowed_websites' => [
        'auth' => [
            'password' => env('ALLOWED_SITE_PASSWORD', 'dGVzdFBhc3N3b3Jk')
        ],
    ],
    'textrail' => [
        'bearer' => env('TEXTRAIL_BEARER', '0m5elzlp6wp7pofevd0jt2i5w6d038mk'),
        'store' => env('TEXTRAIL_STORE', 'trailer_central_t1_sv'),
        'api_url' => env('TEXTRAIL_API_URL', 'https://mcstaging.textrail.com/'),
        'is_guest_checkout' => env('TEXTRAIL_GUEST_CHECKOUT', true),
        'queue' => env('TEXTRAIL_QUEUE', 'ecommerce'),
        'payment_method' => env('TEXTRAIL_PAYMENT_METHOD', 'authnetcim')
    ]
];
