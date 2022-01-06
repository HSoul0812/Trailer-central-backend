<?php

declare(strict_types=1);

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Parts
    |--------------------------------------------------------------------------
    */

    $api->get('inventory/{id}', 'App\Http\Controllers\v1\Inventory\InventoryController@show')->where('id', '[0-9]+');
});
