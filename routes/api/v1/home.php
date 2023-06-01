<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', config('api.routes_throttle'), function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Home page
    |--------------------------------------------------------------------------
    */
    $api->get('/', [App\Http\Controllers\v1\Home\HomeController::class, 'index']);
});
