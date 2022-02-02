<?php


namespace App\Http\Middleware\Ecommerce;

use Illuminate\Support\Facades\Config;

class TexTrailWebhookValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, $next)
    {
        $webookIps = Config::get('textrail.webhook.allowed_ip_addresses');

        $ipList = explode(',', $webookIps);

        if (in_array($request->getClientIp(), $ipList)) {
            return $next($request);
        }

        return response('Access Denied.', 401);
    }
}