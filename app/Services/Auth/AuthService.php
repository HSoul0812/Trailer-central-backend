<?php

namespace App\Services\Auth;

use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Socialite\Facades\Socialite;

class AuthService implements AuthServiceInterface
{
    public function __construct(private WebsiteUserRepositoryInterface $websiteUserRepository)
    {
    }

    public function authenticateSocialCallback($social) {
        $socialUser = Socialite::driver($social)->stateless()->user();
        $users = $this->websiteUserRepository->get(['email' => $socialUser->email]);
        if($users->count() > 0) {

        } else {
            if($social === 'google') {
                $attributes = $this->extractGoogleUserAttributes($socialUser);
                $user = $this->websiteUserRepository->create($attributes);
            }

            if(isset($user)) {
                $user->registration_source = $social;
                $user->save();
            }
        }
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

        event(new Registered($user));
        return $user;
    }

    #[ArrayShape(['email' => "mixed", 'first_name' => "mixed", 'last_name' => "mixed"])]
    protected function extractGoogleUserAttributes($googleUser): array {
        return [
            'email' => $googleUser->email,
            'first_name' => $googleUser->user->given_name,
            'last_name' => $googleUser->user->family_name
        ];
    }
}
