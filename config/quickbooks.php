<?php

return [
    /**
     * The QBO client id
     */
    'client_id' => env('QB_CLIENT_ID'),

    /**
     * The QBO client secret
     */
    'client_secret' => env('QB_CLIENT_SECRET'),

    /**
     * Base URL could be the word 'development' or 'production'
     */
    'base_url' => env('QB_BASE_URL'),
];
