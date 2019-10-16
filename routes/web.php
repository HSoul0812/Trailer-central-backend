<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {    
    return $router->app->version();
});

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
        
    /*
    |--------------------------------------------------------------------------
    | Parts
    |--------------------------------------------------------------------------
    |
    | 
    |
    */
    $api->get('parts', 'App\Http\Controllers\v1\Parts\PartsController@index');
    $api->put('parts', 'App\Http\Controllers\v1\Parts\PartsController@create');
    $api->get('parts/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\PartsController@show');
    $api->post('parts/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\PartsController@update');
    $api->delete('parts/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\PartsController@destroy');
    
    /**
     * Part brands
     */
    $api->get('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@index');
    $api->put('parts/brands', 'App\Http\Controllers\v1\Parts\BrandController@create');
    $api->get('parts/brands/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\BrandController@show');
    $api->post('parts/brands/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\BrandController@update');
    $api->delete('parts/brands/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\BrandController@destroy');
    
    /**
     * Part Categories
     */
    $api->get('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@index');
    $api->put('parts/categories', 'App\Http\Controllers\v1\Parts\CategoryController@create');
    $api->get('parts/categories/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\CategoryController@show');
    $api->post('parts/categories/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\CategoryController@update');
    $api->delete('parts/categories/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\CategoryController@destroy');
    
    /**
     * Part Manufacturers
     */
    $api->get('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@index');
    $api->put('parts/manufacturers', 'App\Http\Controllers\v1\Parts\ManufacturerController@create');
    $api->get('parts/manufacturers/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\ManufacturerController@show');
    $api->post('parts/manufacturers/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\ManufacturerController@update');
    $api->delete('parts/manufacturers/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\ManufacturerController@destroy');
    
    /**
     * Part Types
     */
    $api->get('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@index');
    $api->put('parts/types', 'App\Http\Controllers\v1\Parts\TypeController@create');
    $api->get('parts/types/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\TypeController@show');
    $api->post('parts/types/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\TypeController@update');
    $api->delete('parts/types/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\TypeController@destroy');

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
    $api->get('website/parts/filters', 'App\Http\Controllers\v1\Website\Parts\FilterController@index');
    $api->put('website/parts/filters', 'App\Http\Controllers\v1\Parts\FilterController@create');
    $api->get('website/parts/filters/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\FilterController@show');
    $api->post('website/parts/filters/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\FilterController@update');
    $api->delete('website/parts/filters/{id:[0-9]+}', 'App\Http\Controllers\v1\Parts\FilterController@destroy');
});
