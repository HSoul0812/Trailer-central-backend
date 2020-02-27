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

/*
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->get('parts', 'App\Http\Controllers\v1\Parts\PartsController@index');
    $api->put('parts', 'App\Http\Controllers\v1\Parts\PartsController@create');
    $api->get('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@show');
    $api->post('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@update');
    $api->delete('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@destroy');
});
*/

Route::get('/', function () {
    return view('welcome');
});
