<?php

namespace App\Services\WebsiteUser;

use App\DTOs\User\TcApiResponseUser;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\Captcha\CaptchaServiceInterface;
use App\Services\Integrations\TrailerCentral\Api\Users\UsersServiceInterface;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Carbon;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Socialite\Facades\Socialite;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private CaptchaServiceInterface $captchaService,
        private WebsiteUserRepositoryInterface $websiteUserRepository,
        private UsersServiceInterface $tcUsersService
    )
    {}

    public function authenticateSocialCallback($social): string {
        $socialUser = Socialite::driver($social)->stateless()->user();
        $users = $this->websiteUserRepository->get(['email' => $socialUser->email]);
        if($users->count() > 0) {
            $user = $users->first();
        } else {
            if($social === 'google') {
                $attributes = $this->extractGoogleUserAttributes($socialUser);
                $user = $this->createUser($attributes);
            } else if($social === 'facebook') {
                $attributes = $this->extractFacebookUserAttributes($socialUser);
                $user = $this->createUser($attributes);
            }

            if(isset($user)) {
                $user->email_verified_at = Carbon::now();
                $user->registration_source = $social;
                $user->save();
            }
        }

        return auth('api')->fromUser($user);
    }

    public function authenticateSocial($social, $callback) {
        $socialite = Socialite::driver($social);
        if($callback) {
            $socialite->with([
                'state' => "callback=$callback"
            ]);
        }
        return $socialite->stateless()->redirect();
    }

    public function authenticate(array $attributes): string {
        if(!$this->captchaService->validate($attributes['captcha'])) {
            throw ValidationException::withMessages([
                'captcha' => 'The captcha token is not valid'
            ]);
        }

        if(!$token = auth('api')->attempt([
            'email' => $attributes['email'],
            'password' => $attributes['password']
        ])) {
            throw new UnauthorizedException("Username or password doesn't match");
        }
        return $token;
    }

    public function register(array $attributes) {
        if(!$this->captchaService->validate($attributes['captcha'])) {
            throw ValidationException::withMessages([
                'captcha' => 'The captcha token is not valid'
            ]);
        }

        $user = $this->createUser($attributes);

        event(new Registered($user));
        return $user;
    }

    private function createUser(array $attributes) {
        $tcUser = $this->createTcUser($attributes);

        $attributes['tc_user_id'] = $tcUser->id;
        return  $this->websiteUserRepository->create($attributes);
    }

    private function createTcUser(array $data): TcApiResponseUser {
        $tcAttributes = array_merge([], $data);
        $tcAttributes['name'] = implode(
            ' ', [$tcAttributes['first_name'], $tcAttributes['last_name']]
        );
        $tcAttributes['clsf_active'] = 1;
        $tcAttributes['password'] = \Str::random(12);

        try {
            return $this->tcUsersService->create($tcAttributes);
        } catch(BadResponseException $e) {
            if($e->getCode() === 422) {
                $tcResponse = json_decode($e->getResponse()->getBody());
                if(str_contains($tcResponse->errors->email[0], 'has already')) {
                    throw ValidationException::withMessages([
                        'email' => 'The email has already been taken in TrailerCentral'
                    ]);
                }
            }
            throw $e;
        }
    }

    #[ArrayShape(['email' => "mixed", 'first_name' => "mixed", 'last_name' => "mixed"])]
    private function extractGoogleUserAttributes($googleUser): array {
        return [
            'email' => $googleUser->email,
            'first_name' => $googleUser->user["given_name"],
            'last_name' => $googleUser->user["family_name"]
        ];
    }

    #[ArrayShape(['email' => "mixed", 'first_name' => "mixed", 'last_name' => "mixed"])]
    private function extractFacebookUserAttributes($facebookUser): array {
        $names = explode(' ', $facebookUser->name);
        $firstName = $names[0];
        array_shift($names);
        $lastName = implode(" ", $names);
        return [
            'email' => $facebookUser->email,
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }
}
