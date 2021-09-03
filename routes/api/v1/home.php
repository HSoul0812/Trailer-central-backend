<?php

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Home page
    |--------------------------------------------------------------------------
    */
    $api->get('/', [App\Http\Controllers\v1\Home\HomeController::class, 'index']);
});
