<?php

declare(strict_types=1);

return [
    'refresh_documents' => env('ELASTIC_SCOUT_DRIVER_REFRESH_DOCUMENTS', false),
    'indices' => [
        'inventory' => env('INDEX_INVENTORY', 'inventory')
    ],
    'check_index' => [
        'inventory' => env('ELASTIC_SCOUT_DRIVER_CHECK_INDEX_INVENTORY', true)
    ],
    'settings' => [
        'inventory' => [
            'number_of_shards' => env('ELASTIC_SCOUT_DRIVER_INDEX_SETTINGS_INVENTORY_SHARDS', 5),
            'number_of_replicas' => env('ELASTIC_SCOUT_DRIVER_INDEX_SETTINGS_INVENTORY_REPLICAS', 1),
            'refresh_interval' => env('ELASTIC_SCOUT_DRIVER_INDEX_SETTINGS_REFRESH_INTERVAL', '1s')
        ]
    ],
    'cache' => [
        'ttl' => (int) env('ELASTIC_SCOUT_DRIVER_CACHE_TTL', 28800), // 8 hours
        // @see https://www.php.net/manual/en/function.gzencode.php
        'compression_level' => (int) env('ELASTIC_SCOUT_DRIVER_COMPRESSION_LEVEL', 9),
    ]
];
