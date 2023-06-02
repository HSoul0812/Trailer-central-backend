<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Glossary
    |--------------------------------------------------------------------------
    */
    $api->group(['prefix' => '/email_search'], function ($api) {
        $api->put('subscribe', 'App\Http\Controllers\v1\SubscribeEmailSearch\SubscribeEmailSearchController@create');
    });
});
