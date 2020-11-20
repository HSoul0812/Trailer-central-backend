<?php

namespace App\Http\Middleware\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\User\DealerUser;
use App\Models\User\Interfaces\PermissionsInterface as Permissions;
use App\Models\User\User;
use Closure;

/**
 * Class PermissionMiddleware
 * @package App\Http\Middleware\Inventory
 */
class CreateInventoryPermissionMiddleware
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

        if ($user instanceof User) {
            return $next($request);
        }

        if (!$user instanceof DealerUser) {
            return response('Invalid access token.', 403);
        }

        if (
            !$user->hasPermission(Permissions::INVENTORY, Permissions::SUPER_ADMIN_PERMISSION)
            || !$user->hasPermission(Permissions::INVENTORY, Permissions::CAN_SEE_AND_CHANGE_PERMISSION)
        ) {
            return response('Invalid access token.', 403);
        }

        if (!$user->hasPermission(Permissions::INVENTORY, Permissions::SUPER_ADMIN_PERMISSION)) {
            foreach ($request->request->all() as $key => $param) {
                if (in_array($key, Inventory::SUPER_ADMIN_FIELDS)) {
                    $request->request->remove($key);
                }
            }
        }

        return $next($request);
    }
}
