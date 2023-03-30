<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

/**
 * Manufacturers
 */
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($route) {
    $route->get('manufacturers', 'App\Http\Controllers\v1\Showroom\ShowroomBulkUpdateController@index');
    $route->post('manufacturers/bulk_year', 'App\Http\Controllers\v1\Showroom\ShowroomBulkUpdateController@bulkUpdateYear');
    $route->post('manufacturers/bulk_visibility', 'App\Http\Controllers\v1\Showroom\ShowroomBulkUpdateController@bulkUpdateVisibility');
    $route->post('inventories/bulk_manufacturer', 'App\Http\Controllers\v1\Inventory\InventoryBulkUpdateController@bulkUpdateManufacturer');
});
