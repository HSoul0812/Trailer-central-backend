<?php

namespace App\Http\Middleware\CRM\Text;

use App\Models\User\Integration\Integration;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Class ReplyTextValidate
 * @package App\Http\Middleware\CRM\Text
 */
class ReplyTextValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (Auth::user() instanceof Integration && Auth::user()->hasPermissionCanSeeAndChange(PermissionsInterface::DEALER_TEXTS)) {
            return $next($request);
        }

        return response('Invalid access token.', 403);
    }
}
