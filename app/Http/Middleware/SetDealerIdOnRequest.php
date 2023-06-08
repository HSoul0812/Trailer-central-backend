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

        // When we use integration token, there will be no dealer_id prop because
        // the $user is the instance of the \App\Models\User\Integration\Integration class
        // we only want to set the dealer_id to the request only if it exists in the
        // underlying user model
        if ($user->dealer_id) {
            $request['dealer_id'] = $user->dealer_id;
        }

        if ($user->dealer_user_id) {
            $request['dealer_user_id'] = $user->dealer_user_id;
        }

        return $next($request);
    }
}
