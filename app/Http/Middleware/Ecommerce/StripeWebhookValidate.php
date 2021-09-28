<?php
namespace App\Http\Middleware\Ecommerce;

use Closure;

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
        if ($request->header('Stripe-Signature')) {
            $ipList = explode(',', env('STRIPE_WEBHOOK_ALLOWED_IPS'));

            if (in_array($request->getClientIp(), $ipList)) {
                return $next($request);
            }
        }

        return response('Invalid IP.', 401);
    }
}