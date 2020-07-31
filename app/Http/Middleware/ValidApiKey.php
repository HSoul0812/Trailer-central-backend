<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User\ApiKey;

class ValidApiKey
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
        if ($request->header('api-key')) {
            $apiKey = ApiKey::where('api_key', $request->header('api_key'))->first();
            if ($apiKey) {
                Auth::setUser($apiKey);
                return $next($request);
            }
        }
        
        return response('Invalid api key.', 403);
    }
}
