<?php

declare(strict_types=1);

use App\Http\Controllers\v1\Inventory\InventoryController;
use App\Http\Controllers\v1\Inventory\AttributesController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->group(['prefix' => '/inventory'], function ($api) {
        $api->get('/', [InventoryController::class, 'index'])->middleware(['gzip']);
        $api->put('/', 'App\Http\Controllers\v1\Inventory\InventoryController@create')
            ->middleware('auth:api');

        $api->get('/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@show')->where('id', '[0-9]+')->middleware(['gzip']);
        $api->post('/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@update')
            ->where('id', '[0-9]+')
            ->middleware('auth:api');
        $api->delete('/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@destroy')
            ->where('id', '[0-9]+')
            ->middleware('auth:api');

        $api->get('/{id}/pay/{planId}','App\Http\Controllers\v1\Inventory\InventoryController@pay')
            ->where('id', '[0-9]+')
            ->middleware('auth:api');
        $api->get('/progress', 'App\Http\Controllers\v1\Inventory\InventoryController@getProgress')
            ->middleware('auth:api');
        $api->post('/progress', 'App\Http\Controllers\v1\Inventory\InventoryController@saveProgress')
            ->middleware('auth:api');

        $api->get('/attributes', [AttributesController::class, 'index']);
    });
});
