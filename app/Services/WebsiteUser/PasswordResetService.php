<?php

namespace App\Services\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;
use App\Notifications\WebsiteUserPasswordReset;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordResetService implements PasswordResetServiceInterface
{
    private DatabaseTokenRepository $tokenRepository;

    public function __construct(
        private WebsiteUserRepositoryInterface $userRepository
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

    public function forgetPassword(string $email, ?string $callback): string
    {
        $users = $this->userRepository->get(['email' => $email]);
        if ($users->isEmpty()) {
            throw new NotFoundHttpException("User doesn't exist");
        }
        $user = $users->first();
        if ($this->tokenRepository->recentlyCreatedToken($user)) {
            throw new ThrottleRequestsException('Too many requests');
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
