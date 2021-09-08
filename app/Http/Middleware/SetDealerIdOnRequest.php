<?php

namespace App\Http\Middleware;

use App\Models\User\DealerUser;
use App\Models\User\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class SetDealerIdOnRequest
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
        /** @var User|DealerUser $user */
        $user = Auth::user();

        if (empty($user)) {
            return response('Invalid access token.', 403);
        }

        $request['dealer_id'] = $user->dealer_id;

        if ($user->dealer_user_id) {
            $request['dealer_user_id'] = $user->dealer_user_id;
        }

        return $next($request);
    }
}
