<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Glossary
    |--------------------------------------------------------------------------
    */

    $api->put('subscribe/sent', 'App\Http\Controllers\v1\SubscribeEmailSearch\SubscribeEmailSearchController@create');
});
