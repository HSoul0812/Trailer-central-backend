<?php

declare(strict_types=1);

return [
    'refresh_documents' => env('ELASTIC_SCOUT_DRIVER_REFRESH_DOCUMENTS', false),
    'indices' => [
        'inventory' => env('INDEX_INVENTORY', 'inventory')
    ],
    'ingestion' => [
        'inventory' => env('ELASTIC_SCOUT_DRIVER_INGESTION_TYPE', \App\Indexers\Inventory\SafeIndexer::INGEST_BY_DEALER)
    ],
    'check_index' => [
        'inventory' => env('ELASTIC_SCOUT_DRIVER_CHECK_INDEX_INVENTORY', true)
    ],
    'cache' => [
        'ttl' => env('ELASTIC_SCOUT_DRIVER_CACHE_TTL', 86400), // 24 hours
        // @see https://www.php.net/manual/en/function.gzencode.php
        'compression_level' => env('ELASTIC_SCOUT_DRIVER_COMPRESSION_LEVEL', 9),
    ]
];
