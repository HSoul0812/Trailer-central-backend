<?php

return [
    /**
     * Config related to the inventory indexer
     */
    'indexer' => [
        /**
         * How many inventories it should be in one MakeSearchable job
         */
        'chunk_size' => env('INVENTORY_INDEXER_CHUNK_SIZE', 500),

        /**
         * How many inventories should be processed until we pause temporarily, so we
         * don't flood horizon with big number of jobs
         */
        'sleep_threshold' => env('INVENTORY_INDEXER_SLEEP_THRESHOLD', 10000),

        /**
         * How long the code should pause before start the new round
         */
        'sleep_seconds' => env('INVENTORY_INDEXER_SLEEP_SECONDS', 10),
    ],
];
