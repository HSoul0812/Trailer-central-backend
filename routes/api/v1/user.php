<?php
declare(strict_types=1);

use App\Http\Controllers\v1\WebsiteUser\AuthController;
use App\Http\Controllers\v1\WebsiteUser\PasswordResetController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Auth
    |--------------------------------------------------------------------------
    */

    $api->post('user/register', [AuthController::class, 'create']);
    $api->get('user/auth', [AuthController::class, 'index']);
    $api->get('user/auth/{social}', [AuthController::class, 'social'])
        ->name('SocialAuth')
        ->where('social', 'google|facebook');
    $api->get('user/auth/{social}/callback', [AuthController::class, 'socialCallback'])
        ->name('SocialAuthCallback')
        ->where('social', 'google|facebook');
    $api->post('user/forget-password', [PasswordResetController::class, 'forgetPassword']);
    $api->post('user/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('password.reset');
});
