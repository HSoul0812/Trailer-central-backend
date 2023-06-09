<?php

namespace App\Services\Captcha\Google;

use App\Services\Captcha\CaptchaServiceInterface;
use Http;

class GoogleCaptchaService implements CaptchaServiceInterface
{
    public const API_ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    public function validate(string $response, string $remoteIp = null): bool
    {
        return Http::post(self::API_ENDPOINT, [
            'secret' => config('services.google.captcha.key'),
            'response' => $response,
            'remoteip' => $remoteIp,
        ])->json('score') > config('services.google.captcha.human_threshold');
    }
}
