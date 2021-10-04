<?php
namespace App\Http\Middleware\Ecommerce;

use Closure;
use Illuminate\Support\Facades\Config;

class StripeWebhookValidate
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
        if ($request->header('Allowed-Website')) {
            return $next($request);
        }

        if ($request->header('Stripe-Signature')) {
            $webookIps = Config::get('stripe_checkout.webhook')['allowed_ip_addresses'];

            $ipList = explode(',', $webookIps);

            if (in_array($request->getClientIp(), $ipList)) {
                return $next($request);
            }
        }

        return response('Invalid IP.', 401);
    }
}