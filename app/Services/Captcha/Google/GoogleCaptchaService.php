<?php

namespace App\Services\Captcha\Google;

use App\Services\Captcha\CaptchaServiceInterface;
use GuzzleHttp\Client;

class GoogleCaptchaService implements CaptchaServiceInterface
{
    const API_URL = "https://www.google.com/recaptcha/api/siteverify";
    public function validate(string $response, string $remoteIp = null): bool {
        $secret = config('');
        $httpClient = new Client();
        $response = $httpClient->post(self::API_URL, [
            'form_params' => [
                'secret' => $secret,
                'response' => $response,
                'remoteip' => $remoteIp
            ]
        ]);
        $resultJson = json_decode($response->getBody());
        return $resultJson->success;
    }
}
