<?php

return [
    'allowed_websites' => [
        'auth' => [
            'password' => env('ALLOWED_SITE_PASSWORD', 'dGVzdFBhc3N3b3Jk')
        ],
    ],
    'textrail' => [
        'bearer'=> env('TEXTRAI_BEARER', '0m5elzlp6wp7pofevd0jt2i5w6d038mk')
    ]
];
