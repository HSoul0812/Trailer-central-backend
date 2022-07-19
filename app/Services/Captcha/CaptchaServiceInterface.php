<?php

namespace App\Services\Captcha;

interface CaptchaServiceInterface
{
    public function validate(string $response, string $remoteIp): bool;
}
