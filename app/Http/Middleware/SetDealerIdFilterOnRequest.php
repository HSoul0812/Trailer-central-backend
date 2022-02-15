<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class SetDealerIdFilterOnRequest
 * @package App\Http\Middleware
 */
class SetDealerIdFilterOnRequest
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

        $data['dealer_id']['eq'] = Auth::user()->dealer_id;
        $filter = array_merge($data, $request->get('filter') ?? []);

        $request->merge(['filter' => $filter]);

        return $next($request);
    }
}
