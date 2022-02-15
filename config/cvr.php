<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IDS E-mail
    |--------------------------------------------------------------------------
    |
    | This is the e-mail that IDS provided where they would like to receive
    | the leads we send to them
    |
    */

    'api_endpoint' => env('CVR_API_ENDPOINT', 'https://cvrmanagetest.i-cvr.com/cis/api/cis/import/GenUpload'),
    
    'username' => env('CVR_USERNAME', 'TrailerCentral'),

    'password' => env('CVR_PASSWORD', 'FAE0E114-F81E-4C00-A8A1-A871CC496177'),
    
    'unique_id' => env('CVR_UNIQUE_ID', 'TCTestStore')

];