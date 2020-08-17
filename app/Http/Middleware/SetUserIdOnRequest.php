<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetUserIdOnRequest
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
        if (empty(Auth::user())) {
            return response('Invalid access token.', 403);
        }
        if (empty(Auth::user()->newDealerUser->user_id)) {
            return response('Invalid user id.', 403);
        }
        $request['user_id'] = Auth::user()->newDealerUser->user_id;
        
        return $next($request);
    }
}
