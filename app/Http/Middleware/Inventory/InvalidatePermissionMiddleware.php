<?php

namespace App\Http\Middleware\Inventory;

use Closure;

class InvalidatePermissionMiddleware
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
        if ( $request->header('access-token') === config('integrations.inventory_cache_auth.credentials.access_token') )
        {
            return $next($request);
        }

        return response('Invalid access token.', 403);
    }
}
