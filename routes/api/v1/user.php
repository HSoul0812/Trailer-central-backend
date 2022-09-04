<?php
declare(strict_types=1);

use App\Http\Controllers\v1\WebsiteUser\AuthController;
use App\Http\Controllers\v1\WebsiteUser\PasswordResetController;
use App\Http\Controllers\v1\WebsiteUser\VerificationController;
use App\Http\Controllers\v1\Image\ImageController;

$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    /*
    |--------------------------------------------------------------------------
    | API Auth
    |--------------------------------------------------------------------------
    */
    $api->group(['prefix' => '/user'], function ($api) {
        $api->post('/register', [AuthController::class, 'create']);
        $api->get('/auth', [AuthController::class, 'authenticate']);
        $api->get('/auth/{social}', [AuthController::class, 'social'])
            ->name('SocialAuth')
            ->where('social', 'google|facebook');
        $api->get('/auth/{social}/callback', [AuthController::class, 'socialCallback'])
            ->name('SocialAuthCallback')
            ->where('social', 'google|facebook');
        $api->post('/forget-password', [PasswordResetController::class, 'forgetPassword']);
        $api->get('/reset-password', [PasswordResetController::class, 'showReset'])
            ->name('password.reset');
        $api->post('/reset-password', [PasswordResetController::class, 'resetPassword']);

        /*
        |--------------------------------------------------------------------------
        | Email verification
        |--------------------------------------------------------------------------
        */
        $api->get(
            '/email/verify/{id}/{hash}',
            [VerificationController::class, 'verify']
        )->name('verification.verify');

        $api->get(
            '/email/verification-notification',
            [VerificationController::class, 'resend']
        )->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');
    });

    $api->group(['prefix' => '/user', 'middleware' => 'auth:api'], function ($api) {
        $api->get('', [AuthController::class, 'getProfile']);
        $api->put('', [AuthController::class, 'updateProfile']);
        $api->post('/images', [ImageController::class, 'create']);
    });
});
