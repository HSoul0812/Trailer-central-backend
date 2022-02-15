<?php

/** @noinspection PhpMissingParamTypeInspection */

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User\DealerUser;
use App\Models\User\User;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware to set the dealer id on the request when the user is authenticated.
 */
class SetDealerIdWhenAuthenticatedOnRequest
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var User|DealerUser $user */

        if ($user = Auth::user()) {
            $request['dealer_id'] = $user->dealer_id;

            if ($user->dealer_user_id) {
                $request['dealer_user_id'] = $user->dealer_user_id;
            }
        }

        return $next($request);
    }
}
