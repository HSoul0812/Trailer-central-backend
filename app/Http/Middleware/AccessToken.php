<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User\AuthToken;

class AccessToken
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
        if ($request->has('dealer_id')) {
            Cache::put(env('DEALER_ID_KEY', 'api_dealer_id'), $request->get('dealer_id'));
        }


        if ($request->header('access-token')) {
            $accessToken = AuthToken::where('access_token', $request->header('access-token'))->first();
            if ($accessToken && $accessToken->user) {
                Auth::setUser($accessToken->user);
                return $next($request);
            }
        }

        if ($request->isMethod('get')) {
            return $next($request);
        }
        
        if (strpos($request->url(), 'admin') === false && 
            strpos($request->url(), 'nova-api') === false && 
            strpos($request->url(), 'api/user/login') === false &&
            strpos($request->url(), 'api/user/password-reset/start') === false &&
            strpos($request->url(), 'user/password-reset/finish') === false &&
            strpos($request->url(), 'ecommerce/orders') === false) {
            return response('Invalid access token.', 403);
        }
        
        return $next($request);
    }
}
