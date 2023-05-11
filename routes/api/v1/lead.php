<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Leads
    |--------------------------------------------------------------------------
    */

    $api->put('leads/', 'App\Http\Controllers\v1\Lead\LeadController@create');
});
