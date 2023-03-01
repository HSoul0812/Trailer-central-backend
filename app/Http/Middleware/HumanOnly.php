<?php

namespace App\Http\Middleware;

use Cache;
use Closure;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * With this middleware, we want to allow only the requests that has correct criteria to go through, in this order:
 * 1. IP address is in the config('trailertrader.middlewares.human_only.allow_ips') list OR
 * 2. User agent is in the $allowUserAgents list OR
 * 3. User agent is GoogleBot (we check by IP) OR
 * 4. User agent is not a web crawler (check using the LaravelCrawlerDetect class)
 *
 * If the incoming request failed all these checks, then the code will return empty array, so the bad bots can't tell
 * the different between success and error result
 */
class HumanOnly
{
    /**
     * The cache key for the GoogleBot ip ranges
     */
    const GOOGLE_BOT_IPS_CACHE_KEY = 'google-bot-ips';

    /**
     * The amount of time before the cache will be invalidated
     */
    const GOOGLE_BOT_IPS_CACHE_SECONDS = 86_400;

    /**
     * The URL that store the googlebot ip ranges
     */
    const GOOGLE_BOT_IP_JSON_URL = 'https://developers.google.com/static/search/apis/ipranges/googlebot.json';

    /**
     * List of user agent that we want to allow the request to go through
     *
     * @var string[]
     */
    private array $allowUserAgents = [
        // Postman use this one, and it's being seen as a bot from the Crawler class,
        // so we need to add it here
        'PostmanRuntime',

        // We use trailertrader-frontend on the server.js file of the frontend side
        'trailertrader',
    ];

    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldAllowRequestToGoThrough($request)) {
            return $next($request);
        }

        return response()->json([
            'data' => [],
        ]);
    }

    private function shouldAllowRequestToGoThrough(Request $request): bool
    {
        // Allow request to go through if it's in the allows ip address list
        // even when the user agent is empty
        if ($this->allowIpAddress($request->ip())) {
            return true;
        }

        $userAgent = $request->userAgent();

        // Do not allow empty user agent to go through
        if (empty($userAgent)) {
            return false;
        }

        // Allow request to go through if it's in the allows user agent list
        if ($this->allowUserAgent($userAgent)) {
            return true;
        }

        // Do not allow request to go through if it's from a bot
        // Ref: https://github.com/JayBizzle/Crawler-Detect/blob/master/raw/Crawlers.json
        return !LaravelCrawlerDetect::isCrawler($request->userAgent());
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

    private function allowIpAddress(?string $ip): bool
    {
        // Always decline access from server without IP address
        if ($ip === null) {
            return false;
        }

        // Allows if the IP is in the allow list in the config
        if ($this->ipIsInTheAllowList($ip)) {
            return true;
        }

        // Always allow access from Google Bot
        return $this->isGoogleBotIp($ip);
    }

    private function isGoogleBotIp(string $ip): bool
    {
        $ipRange = $this
            ->getGoogleBotIpRanges()
            ->first(fn(string $ipRange) => IpUtils::checkIp($ip, $ipRange));

        return $ipRange !== null;
    }

    private function getGoogleBotIpRanges(): Collection
    {
        // The keys that we accept from the IP range file
        $acceptKeys = ['ipv6Prefix', 'ipv4Prefix'];

        // We'll get the bot IP list from the file that Google has provided
        // for now I set the cache time to 1 day, so we can keep getting any new
        // IP range that Google might introduce later
        // @see https://developers.google.com/search/docs/crawling-indexing/verifying-googlebot
        return Cache::remember(
            key: self::GOOGLE_BOT_IPS_CACHE_KEY,
            ttl: self::GOOGLE_BOT_IPS_CACHE_SECONDS,
            callback: function () use ($acceptKeys) {
                return Http::get(self::GOOGLE_BOT_IP_JSON_URL)
                    ->collect('prefixes')
                    ->map(function (array $response) use ($acceptKeys) {
                        foreach ($acceptKeys as $acceptKey) {
                            if (array_key_exists($acceptKey, $response)) {
                                return $response[$acceptKey];
                            }
                        }

                        return null;
                    })
                    ->filter()
                    ->values();
            }
        );
    }

    private function ipIsInTheAllowList(string $ip): bool
    {
        $ipList = trim(config('trailertrader.middlewares.human_only.allow_ips'));

        if (empty($ipList)) {
            return false;
        }

        $ips = explode(',', $ipList);

        foreach ($ips as $allowIp) {
            $allowIp = trim($allowIp);

            if ($ip === $allowIp) {
                return true;
            }
        }

        return false;
    }
}
