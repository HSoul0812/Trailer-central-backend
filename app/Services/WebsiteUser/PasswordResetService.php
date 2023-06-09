<?php

namespace App\Services\WebsiteUser;

use App\Domains\Recaptcha\Recaptcha;
use App\Models\WebsiteUser\WebsiteUser;
use App\Notifications\WebsiteUserPasswordReset;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\Captcha\CaptchaServiceInterface;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordResetService implements PasswordResetServiceInterface
{
    public const EXCLUDE_THROTTLE_MAILS = [
        'qa-team@trailercentral.com',
    ];
    private DatabaseTokenRepository $tokenRepository;

    public function __construct(
        private WebsiteUserRepositoryInterface $userRepository,
        private CaptchaServiceInterface $captchaService,
    ) {
        $key = config('app.key');
        $config = config('auth.passwords.website_users');
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $this->tokenRepository = new DatabaseTokenRepository(
            app()['db']->connection(null),
            app()['hash'],
            $config['table'],
            $key,
            $config['expire'],
            $config['throttle'] ?? 0
        );
    }

    public function forgetPassword(string $email, ?string $callback, string $captcha): string
    {
        if (!$this->captchaService->validate($captcha)) {
            throw ValidationException::withMessages([
                'captcha' => Recaptcha::FAILED_CAPTCHA_MESSAGE,
            ]);
        }

        $users = $this->userRepository->get(['email' => $email]);
        if ($users->isEmpty()) {
            throw new NotFoundHttpException("User doesn't exist");
        }
        $user = $users->first();

        if (!in_array($email, self::EXCLUDE_THROTTLE_MAILS)) {
            if ($this->tokenRepository->recentlyCreatedToken($user)) {
                throw new ThrottleRequestsException('Too many requests');
            }
        }

        $token = $this->tokenRepository->create($user);
        WebsiteUserPasswordReset::setResetUrl($callback);
        $user->sendPasswordResetNotification($token);

        return $token;
    }

    public function resetPassword(array $credentials): WebsiteUser
    {
        $users = $this->userRepository->get(['email' => $credentials['email']]);
        if ($users->isEmpty()) {
            throw new NotFoundHttpException("User doesn't exist");
        }

        $user = $users->first();
        if (!$this->tokenRepository->exists($user, $credentials['token'])) {
            throw new NotFoundHttpException('Token not found');
        }
        $user->password = $credentials['password'];
        $user->save();
        $this->tokenRepository->delete($user);

        return $user;
    }
}
