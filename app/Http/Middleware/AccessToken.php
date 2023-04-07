<?php

namespace App\Http\Middleware;

use App\Models\Inventory\Inventory;
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
        $accessTokenHeader = $request->header('access-token');
        $clientId = $request->header('x-client-id');

        // if the request is coming from integration processes, then it will avoid to dispatch many inventory-related jobs
        if ($clientId && $clientId === config('integrations.inventory_cache_auth.credentials.integration_client_id')) {
            Inventory::disableImageOverlayGenerationCacheInvalidationAndSearchSyncing();
        }

        if ($request->has('dealer_id')) {
            Cache::put(env('DEALER_ID_KEY', 'api_dealer_id'), $request->get('dealer_id'));
        }


        if ($accessTokenHeader) {
            $accessToken = AuthToken::where('access_token', $accessTokenHeader)->first();
            if ($accessToken && $accessToken->user) {
                Auth::setUser($accessToken->user);
                return $next($request);
            }
        }

        if ($request->isMethod('get')) {
            return $next($request);
        }

        if ( strpos($request->url(), 'feed/atw') && $accessTokenHeader )
        {
            if ( $accessTokenHeader === config('integrations.atw.credentials.access_token') )
            {
                return $next($request);
            } else
            {
                return response('Invalid access token.', 403);
            }
        } else if (strpos($request->url(), 'feed/atw'))
        {
            return response('Invalid access token.', 403);
        }

        if (strpos($request->url(), 'admin') === false &&
            strpos($request->url(), 'nova-api') === false &&
            strpos($request->url(), 'api/user/login') === false &&
            strpos($request->url(), 'api/user/password-reset/start') === false &&
            strpos($request->url(), 'user/password-reset/finish') === false &&
            strpos($request->url(), 'ecommerce/orders') === false &&
            preg_match('/api\/website\/[0-9]*\/user/', $request->url()) === false &&
            strpos($request->url(), 'user/password-reset/finish') === false) {
            return response('Invalid access token.', 403);
        }

        return $next($request);
    }
}
