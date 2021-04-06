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

    'email' => env('IDS_EMAIL', 'CRMLeadSetup@IDS-Astra.com'),
    
    'export_start_date' => env('IDS_EXPORT_START_DATE', '2021-01-13 00:00:00'),

    'copied_emails' => env('COPIED_EMAILS', 'alberto@trailercentrail.com')

];
