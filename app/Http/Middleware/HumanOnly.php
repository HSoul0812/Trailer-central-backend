<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect;
use Symfony\Component\HttpFoundation\Response;

class HumanOnly
{
    /**
     * List of user agent that we want to allow the request to go through
     *
     * @var string[]
     */
    private array $allowUserAgents = [
        // Postman use this one, and it's being seen as a bot from the Crawler class,
        // so we need to add it here
        'PostmanRuntime',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!$this->shouldAllowRequestToGoThrough($request)) {
            return response()->json([
                'data' => [],
            ]);
        }

        return $next($request);
    }

    private function shouldAllowRequestToGoThrough(Request $request): bool
    {
        $userAgent = $request->userAgent();

        // Do not allow empty user agent to go through
        if (empty($userAgent)) {
            return false;
        }

        // Allow request to go through if it's in the allow list
        if ($this->allowUserAgent($userAgent)) {
            return true;
        }

        // Do not allow request to go through if it's from a bot
        // Ref: https://github.com/JayBizzle/Crawler-Detect/blob/master/raw/Crawlers.json
        return !LaravelCrawlerDetect::isCrawler();
    }

    private function allowUserAgent(string $userAgent): bool
    {
        foreach ($this->allowUserAgents as $allowUserAgent) {
            if (str_contains($userAgent, $allowUserAgent)) {
                return true;
            }
        }

        return false;
    }
}
