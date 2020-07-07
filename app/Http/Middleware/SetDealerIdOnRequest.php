<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetDealerIdOnRequest
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
        $request['dealer_id'] = Auth::user()->dealer_id;
        
        return $next($request);
    }
}
