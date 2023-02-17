<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            return parent::handle($request, $next, ...$guards);
        } catch (AuthenticationException $exception) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                ], Response::HTTP_UNAUTHORIZED);
            }

            throw $exception;
        }
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function redirectTo($request): ?string
    {
        // Letting the request from the API goes to the route below will cause an error
        if ($request->is('api/*')) {
            return null;
        }

        if (!$request->expectsJson()) {
            return route('login');
        }

        return null;
    }
}
