<?php

namespace App\Http\Middleware;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use App\Transformers\Inventory\InventoryElasticSearchOutputTransformer;
use Dingo\Api\Http\Request;
use Closure;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ThrottleRequestsByUserAgent extends ThrottleRequests
{
    use Helpers;

    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @param $maxAttempts
     * @param $decayMinutes
     * @param $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if (Str::contains(strtolower($request->userAgent()), $this->getBlackList())) {
            return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
        }

        $key = $prefix.$this->resolveRequestSignature($request);
        $response = $next($request);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    private function getBlackList(): array
    {
        return config('security.rate_limiting.user_agent.black_list');
    }
}
