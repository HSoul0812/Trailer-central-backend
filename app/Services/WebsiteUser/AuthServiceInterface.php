<?php

namespace App\Services\WebsiteUser;

interface AuthServiceInterface
{
    public function authenticateSocial($social);

    public function authenticateSocialCallback($social);

    public function authenticate(array $credential);

    public function register(array $attributes);
}
