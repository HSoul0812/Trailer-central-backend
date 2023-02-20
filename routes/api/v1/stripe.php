<?php
declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix' => '/stripe'], function ($api) {
        $api->post('/webhook', 'App\Http\Controllers\v1\Stripe\StripeController@webhook');
        $api->get('/plans', 'App\Http\Controllers\v1\Stripe\StripeController@plans');
    });
});
