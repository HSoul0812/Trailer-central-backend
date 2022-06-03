<?php

namespace App\Services\Auth;

use App\Repositories\WebsiteUser\WebsiteUserRepository;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use Laravel\Socialite\Facades\Socialite;

class AuthService implements AuthServiceInterface
{
    public function __construct(private WebsiteUserRepositoryInterface $websiteUserRepository)
    {
    }

    public function authenticateSocialCallback($social) {
        $user = Socialite::driver($social)->stateless()->user();
        \Log::info(json_encode($user));
    }

    public function authenticateSocial($social) {
        return Socialite::driver($social)->stateless()->redirect();
    }

    public function authenticate() {
    }

    public function register(array $data) {
        $this->websiteUserRepository->create($request->all());
    }
}
