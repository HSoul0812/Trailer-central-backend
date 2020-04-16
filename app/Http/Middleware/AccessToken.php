<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User\User;
use App\Models\User\AuthToken;
use Illuminate\Support\Facades\Cache;

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

        if ($request->isMethod('get')) {
            return $next($request);
        }

        if ($request->header('access-token')) {
            $accessToken = AuthToken::where('access_token', $request->header('access-token'))->first();
            if ($accessToken && $accessToken->user) {
                $request['dealer_id'] = $accessToken->user->dealer_id;
                return $next($request);
            }
        }
        
        if (strpos($request->url(), 'admin') === false && strpos($request->url(), 'nova-api') === false) {
            return response('Invalid access token.', 403);
        }
        
        return $next($request);
    }
}
