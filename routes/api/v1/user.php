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
    $api->group(['prefix' => '/user'], function ($api) {
        $api->post('/register', [AuthController::class, 'create']);
        $api->get('/auth', [AuthController::class, 'index']);
        $api->get('/auth/{social}', [AuthController::class, 'social'])
            ->name('SocialAuth')
            ->where('social', 'google|facebook');
        $api->get('/auth/{social}/callback', [AuthController::class, 'socialCallback'])
            ->name('SocialAuthCallback')
            ->where('social', 'google|facebook');
        $api->post('/forget-password', [PasswordResetController::class, 'forgetPassword']);
        $api->post('/reset-password', [PasswordResetController::class, 'resetPassword'])
            ->name('password.reset');
    });
});
