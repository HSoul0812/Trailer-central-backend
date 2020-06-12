<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User\AuthToken;

class ValidAccessToken
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
            $accessToken = AuthToken::where('access_token', $request->header('access-token'))->first();
            if ($accessToken && $accessToken->user) {
                $request['dealer_id'] = $accessToken->user->dealer_id;
                return $next($request);
            }
        }
        
        return response('Invalid access token.', 403);
    }
}
