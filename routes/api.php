<?php

use Dingo\Api\Routing\Router;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** @var Router $api */
/** @var Router $route */

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($route) {

    /*
    |--------------------------------------------------------------------------
    | Parts
    |--------------------------------------------------------------------------
    |
    |
    |
    */
    $route->get('parts', 'App\Http\Controllers\v1\Parts\PartsController@index');
    $route->put('parts', 'App\Http\Controllers\v1\Parts\PartsController@create');
    $route->get('parts/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\PartsController@show');
    $route->post('parts/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\PartsController@update');
    $route->delete('parts/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\PartsController@destroy');

    /**
     * Part brands
     */
    $route->get('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@index');
    $route->put('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@create');
    $route->get('parts/brands/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\BrandController@show');
    $route->post('parts/brands/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\BrandController@update');
    $route->delete('parts/brands/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\BrandController@destroy');

    /**
     * Part Categories
     */
    $route->get('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@index');
    $route->put('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@create');
    $route->get('parts/categories/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\CategoryController@show');
    $route->post('parts/categories/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\CategoryController@update');
    $route->delete('parts/categories/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\CategoryController@destroy');

    /**
     * Part Manufacturers
     */
    $route->get('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@index');
    $route->put('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@create');
    $route->get('parts/manufacturers/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\ManufacturerController@show');
    $route->post('parts/manufacturers/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\ManufacturerController@update');
    $route->delete('parts/manufacturers/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\ManufacturerController@destroy');

    /**
     * Part Types
     */
    $route->get('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@index');
    $route->put('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@create');
    $route->get('parts/types/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\TypeController@show');
    $route->post('parts/types/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\TypeController@update');
    $route->delete('parts/types/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\TypeController@destroy');

    /**
     * Part Bulk download
     */
    $route->post('parts/bulk/download', 'App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController@create');

    /**
     * Part Bulk
     */
    $route->get('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@index');
    $route->post('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@create');
    $route->get('parts/bulk/{id:[0-9]+}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@show');
    $route->put('parts/bulk/{id:[0-9]+}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@update');
    $route->delete('parts/bulk/{id:[0-9]+}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@destroy');


    /*
    |--------------------------------------------------------------------------
    | Website
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Website Part Filters
     */
    $route->get('website/parts/filters', 'App\Http\Controllers\v1\Website\Parts\FilterController@index');
    $route->put('website/parts/filters', 'App\Http\Controllers\v1\Parts\FilterController@create');
    $route->get('website/parts/filters/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\FilterController@show');
    $route->post('website/parts/filters/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\FilterController@update');
    $route->delete('website/parts/filters/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\FilterController@destroy');

    /*
    |--------------------------------------------------------------------------
    | Vendors
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    /**
     * Vendors
     */
    $route->get('vendors', 'App\Http\Controllers\v1\Parts\VendorController@index');
    $route->put('vendors', 'App\Http\Controllers\v1\Parts\VendorController@create');
    $route->get('vendors/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\VendorController@show');
    $route->post('vendors/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\VendorController@update');
    $route->delete('vendors/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\VendorController@destroy');

});
