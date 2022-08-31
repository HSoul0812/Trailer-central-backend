<?php declare(strict_types=1);

return [
    'refresh_documents' => env('ELASTIC_SCOUT_DRIVER_REFRESH_DOCUMENTS', false),
    'indices' => [
        'inventory' => env('INDEX_INVENTORY', 'inventory')
    ],
    'expiration_index_check_time'=> env('ELASTIC_SCOUT_DRIVER_EXPIRATION_INDEX_CHECK_TIME', 3600)
];
