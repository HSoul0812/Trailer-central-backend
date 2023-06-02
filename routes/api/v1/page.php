<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', config('api.routes_throttle'), function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Glossary
    |--------------------------------------------------------------------------
    */

    $api->get('pages', 'App\Http\Controllers\v1\Page\PageController@index');
});
