<?php

declare(strict_types=1);

use App\Http\Controllers\v1\Inventory\InventoryController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix' => '/inventory'], function ($api) {
        $api->get('/', [InventoryController::class, 'index']);
        $api->get('/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@show')->where('id', '[0-9]+');
    });
});
