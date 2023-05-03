<?php

namespace App\Providers;

use App\Services\Captcha\CaptchaServiceInterface;
use App\Services\Captcha\Google\GoogleCaptchaService;
use Illuminate\Support\ServiceProvider;

class CaptchaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CaptchaServiceInterface::class, GoogleCaptchaService::class);
    }
}
