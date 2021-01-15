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
    
    'copied_emails' => env('COPIED_EMAILS', 'alberto@trailercentrail.com')

];
