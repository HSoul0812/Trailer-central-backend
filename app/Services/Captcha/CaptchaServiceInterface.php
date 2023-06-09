<?php

namespace App\Services\Captcha;

interface CaptchaServiceInterface
{
    public function validate(string $response): bool;
}
