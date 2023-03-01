<?php

namespace App\Http\Middleware\InteractionIntegration;

use App\Models\User\Integration\Integration;
use Arr;
use Auth;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IntegrationPermission
{
    public function handle(Request $request, Closure $next, ...$features)
    {
        $user = Auth::user();

        if (!$user instanceof Integration || count($features) === 0) {
            return $next($request);
        }

        $permissionLevel = Arr::get($features, 1);

        $hasPermission = $user->perms()
            ->where('feature', $features[0])
            ->when($permissionLevel !== null, function(Builder $query) use ($permissionLevel) {
               $query->where('permission_level', 'like', "$permissionLevel%");
            })
            ->exists();

        if ($hasPermission) {
            return $next($request);
        }

        return response()->json([
            'message' => "You don't have permission to use this route.",
        ], Response::HTTP_FORBIDDEN);
    }
}
