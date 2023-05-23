<?php

declare(strict_types=1);

use App\Http\Controllers\v1\ViewedDealer\ViewedDealerController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix' => '/viewed-dealers'], function ($api) {
        $api->get('/', [ViewedDealerController::class, 'index']);
        $api->post('/', [ViewedDealerController::class, 'create']);
    });
    $api->group(['prefix' => '/dealers'], function ($api) {
        $api->get('/', [ViewedDealerController::class, 'getDealers']);
    });
});
