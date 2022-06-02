<?php

namespace App\Services\Auth;

use Hybridauth\Hybridauth;
use Laravel\Socialite\Facades\Socialite;

class AuthService implements AuthServiceInterface
{
    public function authenticateSocialCallback($social) {
        $user = Socialite::driver($social)->stateless()->user();
        \Log::info(json_encode($user));
    }

    public function authenticateSocial($social) {
        return Socialite::driver($social)->stateless()->redirect();
    }

    public function authenticate() {
    }
}
