<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User\User;
use App\Models\User\DealerUser;
use Illuminate\Support\Facades\Auth;

class ValidateDealerIdOnRequest
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

        if (empty($user) || !($user instanceof User)) {
            return response('Invalid access token.', 403);
        }

        $request['dealer_id'] = $user->dealer_id;

        return $next($request);
    }
}
