<?php

return [
    'webhook' => [
        'allowed_ip_addresses' => env('STRIPE_WEBHOOK_ALLOWED_IPS', '3.18.12.63,3.130.192.231,13.235.14.237,13.235.122.149,18.211.135.69,35.154.171.200,52.15.183.38,54.88.130.119,54.88.130.237,54.187.174.169,54.187.205.235,54.187.216.72'),
    ],
    'secret' => env('STRIPE_SECRECT_KEY', 'sk_test_abcde1234567890')
];