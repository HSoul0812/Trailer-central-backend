<?php

return [
    'webhook' => [
        'allowed_ip_addresses' => env('TEXTRAIL_WEBHOOK_ALLOWED_IPS', '127.0.0.1,3.18.12.63,3.130.192.231'),
    ],
];