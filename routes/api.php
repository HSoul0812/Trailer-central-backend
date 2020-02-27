<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('parts', 'App\Http\Controllers\v1\Parts\PartsController@index');
Route::put('parts', 'App\Http\Controllers\v1\Parts\PartsController@create');
Route::get('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@show');
Route::post('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@update');
Route::delete('parts/{id}', 'App\Http\Controllers\v1\Parts\PartsController@destroy');
