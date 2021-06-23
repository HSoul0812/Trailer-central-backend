<?php

namespace App\Http\Middleware\Inventory;

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
    const SUPER_ADMIN_FIELDS = [
        'true_cost',
        'pac_amount',
        'pac_type',
        'fp_balance',
        'fp_committed',
        'fp_paid',
        'fp_interest_paid',
        'fp_vendor',
        'l_holder',
        'l_attn',
        'l_name_on_account',
        'l_address',
        'l_account',
        'l_city',
        'l_state',
        'l_zip_code',
        'l_payoff',
        'l_phone',
        'l_paid',
        'l_fax',
    ];

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
            && !$user->hasPermission(Permissions::INVENTORY, Permissions::CAN_SEE_AND_CHANGE_PERMISSION)
        ) {
            return response('Invalid access token.', 403);
        }

        if (!$user->hasPermission(Permissions::INVENTORY, Permissions::SUPER_ADMIN_PERMISSION)) {

            foreach ($request->request->all() as $key => $param) {
                if (in_array($key, self::SUPER_ADMIN_FIELDS)) {
                    unset($request[$key]);
                    $request->request->remove($key);
                }
            }
        }

        return $next($request);
    }
}
