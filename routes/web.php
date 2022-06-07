<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    redirect('/api');
});

Route::get(
    '/email/verify/{id}/{hash}',
    'App\Http\Controllers\v1\Auth\VerificationController@verify'
)->name('verification.verify');

Route::post(
    '/email/verification-notification',
    'App\Http\Controllers\v1\Auth\VerificationController@resend'
)->middleware(['auth', 'throttle:6,1'])->name('verification.send');
