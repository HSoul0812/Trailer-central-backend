<?php

namespace App\Http\Middleware\User;

use App\Models\User\DealerUser;
use App\Models\User\Interfaces\PermissionsInterface as Permissions;
use App\Models\User\User;
use Closure;

class ManageAccountPermissionMiddleware
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
        $user = $request->user();

        /**
         * Primary User Automatically Gets Access
         */
        if ($user instanceof User) {
            return $next($request);
        }

        /**
         * Must be a Secondary User to Get Access
         */
        if (!$user instanceof DealerUser) {
            return response('Invalid access token.', 403);
        }

        /**
         * Validate Secondary User Has Permission to Change Account Settings
         */
        if (
            !$user->hasPermission(Permissions::ACCOUNTS, Permissions::SUPER_ADMIN_PERMISSION) &&
            !$user->hasPermission(Permissions::ACCOUNTS, Permissions::CAN_SEE_AND_CHANGE_PERMISSION)
        ) {
            return response('Invalid access token.', 403);
        }

        return $next($request);
    }
}