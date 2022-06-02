<?php

namespace App\Services\Auth;

interface AuthServiceInterface
{
    public function authenticateSocial($social);

    public function authenticateSocialCallback($social);

    public function authenticate();
}
