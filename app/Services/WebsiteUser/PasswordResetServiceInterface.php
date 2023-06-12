<?php

namespace App\Services\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;

interface PasswordResetServiceInterface
{
    public function forgetPassword(string $email, string $callback, string $captcha): string;

    public function resetPassword(array $credentials): WebsiteUser;
}
