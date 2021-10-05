<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Website\Website;

class SetWebsiteIdOnRequest
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

        // Get Website?!
        $dealerId = Auth::user()->dealer_id;
        $website = Website::whereDealerId($request['dealer_id'])->first();
        if(!empty($website['id'])) {
            $request['website_id'] = $website['id'];
        }
        
        return $next($request);
    }
}