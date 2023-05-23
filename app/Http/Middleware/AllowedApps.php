<?php

namespace App\Http\Middleware;

use App;
use App\Models\AppToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowedApps
{
    public const APP_TOKEN_PARAM_NAME = 'app-token';

    public function handle(Request $request, Closure $next)
    {
        // We'll use the one from param meter or request body first if it exists
        // otherwise, get one from the bearer token
        $tokenAcquireOrder = [
            $request->input(self::APP_TOKEN_PARAM_NAME),
            $request->bearerToken(),
        ];

        // Get the first one that isn't null
        $appTokenFromRequest = collect($tokenAcquireOrder)->filter()->first();

        if ($appTokenFromRequest === null) {
            return response()->json([
                'message' => "Please provide 'app-token' in query param or request body, or a bearer token.",
            ], Response::HTTP_BAD_REQUEST);
        }

        $appToken = AppToken::where('token', $appTokenFromRequest)->first();

        if ($appToken === null) {
            return response()->json([
                'message' => "Invalid App Token: $appTokenFromRequest.",
            ], Response::HTTP_BAD_REQUEST);
        }

        App::instance(AppToken::class, $appToken);

        return $next($request);
    }
}
