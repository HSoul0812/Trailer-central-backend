<?php

namespace App\Services\WebsiteUser;

interface AuthServiceInterface
{
    public function authenticateSocial($social, $callback);

    public function authenticateSocialCallback($social): string;

    public function authenticate(array $credential): string;

    public function register(array $attributes);

    public function update(int $id, array $attributes): bool;
}
