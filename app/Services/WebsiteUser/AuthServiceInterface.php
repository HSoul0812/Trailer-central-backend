<?php

namespace App\Services\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;

interface AuthServiceInterface
{
    public function authenticateSocial($social, $callback);

    public function authenticateSocialCallback($social): string;

    public function authenticate(array $credential): string;

    public function register(array $attributes);

    public function update(WebsiteUser $user, array $attributes): bool;

    public function createTcUserIfNotExist(WebsiteUser $user);
}
