<?php
declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Auth
    |--------------------------------------------------------------------------
    */

    $api->get('auth', 'App\Http\Controllers\v1\Auth\AuthController@index');
    $api->get('auth/{social}', 'App\Http\Controllers\v1\Auth\AuthController@social')
        ->name('SocialAuth')
        ->where('social', 'google|facebook');
    $api->get('auth/{social}/callback', 'App\Http\Controllers\v1\Auth\AuthController@socialCallback')
        ->name('SocialAuthCallback')
        ->where('social', 'google|facebook');
});
