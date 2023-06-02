<?php

namespace App\Http\Middleware;

use App\Http\Clients\ElasticSearch\ElasticSearchQueryResult;
use App\Transformers\Inventory\InventoryElasticSearchOutputTransformer;
use Dingo\Api\Http\Request;
use Closure;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Str;

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
        $key = $prefix.$this->resolveRequestSignature($request);
        $response = $next($request);

        if (Str::contains(strtolower($request->userAgent()), $this->getBlackList())) {
            $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

            if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
                // bots will be happy with this result, then probably they will not mutate
                $result = new ElasticSearchQueryResult([], [], 0, []);

                $response = $this->response
                    ->collection($result->hints, new InventoryElasticSearchOutputTransformer())
                    ->addMeta('aggregations', $result->aggregations)
                    ->addMeta('total', $result->total);
            }

            $this->limiter->hit($key, $decayMinutes * 60);
        }


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
