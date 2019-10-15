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

Route::get('parts', 'v1\Parts\PartsController@index');
Route::put('parts', 'v1\Parts\PartsController@create');
Route::get('parts/{id}', 'v1\Parts\PartsController@show');
Route::post('parts/{id}', 'v1\Parts\PartsController@update');
Route::delete('parts/{id}', 'v1\Parts\PartsController@destroy');

