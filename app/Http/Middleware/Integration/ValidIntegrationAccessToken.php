<?php

namespace App\Http\Middleware\Integration;

use Closure;
use App\Models\User\AuthToken;

/**
 * Class ValidIntegrationAccessToken
 * @package App\Http\Middleware\Integration
 */
class ValidIntegrationAccessToken
{
    public function handle($request, Closure $next)
    {
        if ($request->header('access-token') && $request->get('integration_name')) {
            $accessToken = AuthToken::where('access_token', $request->header('access-token'))->first();

            if ($accessToken && $accessToken->user && $accessToken->user->name === $request->get('integration_name')) {
                return $next($request);
            }
        }

        return response('Invalid access token.', 403);
    }
}
