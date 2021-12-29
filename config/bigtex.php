<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BigTex
    |--------------------------------------------------------------------------
    |
    | BigTex export secrets
    |
    */

    'api_endpoint' => env('BIGTEX_API_ENDPOINT', 'https://www.formstack.com/api/v2/'),
    
    'access_token' => env('BIGTEX_ACCESS_TOKEN', 'trailercentral'),

    'form_id' => env('BIGTEX_FORM_ID', '4541647')

];