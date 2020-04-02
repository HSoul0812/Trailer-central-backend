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
    $route->get('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@show')->where('id', '[0-9]+');
    $route->post('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@update')->where('id', '[0-9]+');
    $route->delete('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@destroy')->where('id', '[0-9]+');

    /**
     * Part brands
     */
    $route->get('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@index');
    $route->put('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@create');
    $route->get('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@show')->where('id', '[0-9]+');
    $route->post('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@update')->where('id', '[0-9]+');
    $route->delete('parts/brands/{id}', 'App\Http\Controllers\v1\Parts\BrandController@destroy')->where('id', '[0-9]+');

    /**
     * Part Categories
     */
    $route->get('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@index');
    $route->put('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@create');
    $route->get('parts/categories/{id}', 'App\Http\Controllers\v1\Parts\CategoryController@show')->where('id', '[0-9]+');
    $route->post('parts/categories/{id}', 'App\Http\Controllers\v1\Parts\CategoryController@update')->where('id', '[0-9]+');
    $route->delete('parts/categories/{id}', 'App\Http\Controllers\v1\Parts\CategoryController@destroy')->where('id', '[0-9]+');

    /**
     * Part Manufacturers
     */
    $route->get('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@index');
    $route->put('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@create');
    $route->get('parts/manufacturers/{id}', 'App\Http\Controllers\v1\Parts\ManufacturerController@show')->where('id', '[0-9]+');
    $route->post('parts/manufacturers/{id}', 'App\Http\Controllers\v1\Parts\ManufacturerController@update')->where('id', '[0-9]+');
    $route->delete('parts/manufacturers/{id}', 'App\Http\Controllers\v1\Parts\ManufacturerController@destroy')->where('id', '[0-9]+');

    /**
     * Part Types
     */
    $route->get('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@index');
    $route->put('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@create');
    $route->get('parts/types/{id}', 'App\Http\Controllers\v1\Parts\TypeController@show')->where('id', '[0-9]+');
    $route->post('parts/types/{id}', 'App\Http\Controllers\v1\Parts\TypeController@update')->where('id', '[0-9]+');
    $route->delete('parts/types/{id}', 'App\Http\Controllers\v1\Parts\TypeController@destroy')->where('id', '[0-9]+');

    /**
     * Part Bulk
     */
    $route->get('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@index');
    $route->post('parts/bulk', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@create');
    $route->get('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@show')->where('id', '[0-9]+');
    $route->put('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@update')->where('id', '[0-9]+');
    $route->delete('parts/bulk/{id}', 'App\Http\Controllers\v1\Bulk\Parts\BulkUploadController@destroy')->where('id', '[0-9]+');


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
    $route->get('website/parts/filters/{id}', 'App\Http\Controllers\v1\Parts\FilterController@show')->where('id', '[0-9]+');
    $route->post('website/parts/filters/{id}', 'App\Http\Controllers\v1\Parts\FilterController@update')->where('id', '[0-9]+');
    $route->delete('website/parts/filters/{id}', 'App\Http\Controllers\v1\Parts\FilterController@destroy')->where('id', '[0-9]+');

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
    $route->get('vendors/{id}', 'App\Http\Controllers\v1\Parts\VendorController@show')->where('id', '[0-9]+');
    $route->post('vendors/{id}', 'App\Http\Controllers\v1\Parts\VendorController@update')->where('id', '[0-9]+');
    $route->delete('vendors/{id}', 'App\Http\Controllers\v1\Parts\VendorController@destroy')->where('id', '[0-9]+');

});
