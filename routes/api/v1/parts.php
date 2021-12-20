<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Parts
    |--------------------------------------------------------------------------
    */

    $api->get('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@index');
});
