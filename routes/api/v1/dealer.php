<?php

declare(strict_types=1);

use App\Http\Controllers\v1\Dealer\DealerController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix' => '/dealers'], function ($api) {
        $api->get('/', [DealerController::class, 'index']);
    });
});
