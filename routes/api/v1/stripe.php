<?php
declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->post('/stripe/webhook', 'App\Http\Controllers\v1\Stripe\StripeController@webhook');
});
