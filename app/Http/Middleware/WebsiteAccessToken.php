<?php

namespace App\Http\Middleware;

use App\Models\Website\User\WebsiteUserToken;
use Closure;
use Dingo\Api\Auth\Auth;

class WebsiteAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('access-token')) {
            $accessToken = WebsiteUserToken::where('access_token', $request->header('access-token'))->first();
            if ($accessToken && $accessToken->user) {
                app(Auth::class)->setUser($accessToken->user);
                return $next($request);
            }
        }

        if ($request->isMethod('get')) {
            return $next($request);
        }

        return response('Invalid access token.', 403);
    }
}
