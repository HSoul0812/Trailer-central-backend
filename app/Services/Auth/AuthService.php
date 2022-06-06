<?php

namespace App\Services\Auth;

use App\Repositories\WebsiteUser\WebsiteUserRepository;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
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

    public function register(array $attributes) {
        $attributes['password'] = Hash::make($attributes['password']);
        $user = $this->websiteUserRepository->create($attributes);
        $user->save();

//        event(new Registered($user));
        return $user;
    }
}
