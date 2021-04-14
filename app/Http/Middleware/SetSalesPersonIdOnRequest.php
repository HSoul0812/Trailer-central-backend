<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetSalesPersonIdOnRequest
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
        if(!empty(Auth::user()->sales_person->id)) {
            $request['sales_person_id'] = Auth::user()->sales_person->id;
        }
        
        return $next($request);
    }
}
