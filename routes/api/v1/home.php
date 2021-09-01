<?php

use App\Http\Controllers\v1\Home\HomeController;

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Home page
    |--------------------------------------------------------------------------
    */    
    $api->get('/', [HomeController::class, 'index']);
});
