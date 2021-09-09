<?php

return [
    /*
     |--------------------------------------------------------------------------
     | TrailerCentral
     |--------------------------------------------------------------------------
     |
     | This option controls the default memory to be allocated on importing time.
     |
     | The synchronization use an in-memory buffer to do not make an insertion
     | per record, instead of that, it stores in-memory the DML to insert
     | multiple records, thus giving better performance.
     |
     | It must be specified on megabytes, e.g: "550M", "650M", "850M"
     |
     | Note: when `records_per_bulk` is increased, `memory_limit` must be
     |       increased as well, e.g: 8000 records should have 650M
     */

    'memory_limit' => env('TC_SYNCHRONIZATION_MEMORY_LIMIT', '650M'),

    'records_per_chunk' => env('TC_SYNCHRONIZATION_RECORDS_PER_CHUNK', 8000),
];
