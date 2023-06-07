<?php

namespace App\Http\Middleware;

use App\Domains\Crawlers\Strategies\CrawlerCheckStrategy;
use Cache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * With this middleware, we want to allow only the requests that has correct criteria to go through, in this order:
 * 1. IP address is in the config('trailertrader.middlewares.human_only.allow_ips') list OR
 * 2. User agent is GoogleBot or BingBot (we check by IP) OR
 * 3. User agent is in the $allowUserAgents list OR
 * 4. User agent is not a web crawler (check using the LaravelCrawlerDetect class).
 *
 * If the incoming request failed all these checks, then the code will return empty array, so the bad bots can't tell
 * the different between success and error result
 */
class HumanOnly
{
    public const TT_SSR_ORIGINAL_IP_HEADER = 'TT-SSR-Original-IP';

    /**
     * List of user agent that we want to allow the request to go through.
     *
     * @var string[]
     */
    private array $allowUserAgents = [
        // Postman use this one, and it's being seen as a bot from the Crawler class,
        // so we need to add it here
        'PostmanRuntime',

        // We use trailertrader-frontend on the server.js file of the frontend side
        'trailertrader',

        // We'll allow access from DW too
        'bens-playground',

        // We'll allow Google Bots
        // @see https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers
        'APIs-Google',
        'AdsBot-Google-Mobile',
        'AdsBot-Google',
        'Mediapartners-Google',
        'Googlebot-Image',
        'Googlebot',
        'Googlebot-News',
        'GoogleProducer',
        'Googlebot-Video',
        'GoogleOther',
        'AdsBot-Google-Mobile-Apps',
        'FeedFetcher-Google',
        'Google-Read-Aloud',
        'Storebot-Google',
        'Google-Site-Verification',

        // Bingbot
        'Bingbot',
        'AdIdxBot',
        'BingPreview',
        'MicrosoftPreview',
    ];

    private array $blackListedUserAgents = [
        'YandexBot',
        'Clickagy Intelligence Bot',
        'SemrushBot',
        'PetalBot',
        'SirdataBot',
        'CriteoBot',
        'Quantcastbot',
        'AhrefsBot',
        'Mail.RU_Bot/Fast/2.0',
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
        // We'll try to get the ip from the header first (TT frontend assigns this)
        // if that doesn't exist, we'll get from the $_SERVER variable
        $ip = $request->headers->get(self::TT_SSR_ORIGINAL_IP_HEADER, $request->ip());

        // Allow request to go through if it's in the allows ip address list
        // even when the user agent is empty
        if ($this->allowIpAddress($ip)) {
            return true;
        }

        $userAgent = $request->userAgent();

        // Do not allow empty user agent to go through
        if (empty($userAgent)) {
            return false;
        }

        // Make it lowercase so word like Bingbot becomes bingbot
        $userAgent = strtolower($userAgent);

        if ($this->isBlackListedUserAgent($userAgent)) {
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
            if (str_contains($userAgent, strtolower($allowUserAgent))) {
                return true;
            }
        }

        // Loop from the crawler config, take only the one that has strategy as user agent check
        foreach (config('crawlers.providers') as $config) {
            if ($config['strategy'] !== CrawlerCheckStrategy::USER_AGENT_CHECK) {
                continue;
            }

            foreach ($config['user_agents'] as $allowUserAgent) {
                if (str_contains($userAgent, strtolower($allowUserAgent))) {
                    return true;
                }
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

        return $this->isIpOfTheAllowedBots($ip);
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

    private function isIpOfTheAllowedBots(string $ip): bool
    {
        foreach (config('crawlers.providers') as $config) {
            if ($config['strategy'] !== CrawlerCheckStrategy::IP_CHECK) {
                continue;
            }

            /** @var Collection $ipRanges */
            $ipRanges = Cache::get($config['ips_cache_key'], collect([]));

            $ipRange = $ipRanges->first(fn (string $ipRange) => IpUtils::checkIp($ip, $ipRange));

            if ($ipRange !== null) {
                return true;
            }
        }

        return false;
    }

    private function isBlackListedUserAgent(string $userAgent): bool
    {
        foreach ($this->blackListedUserAgents as $blackListedUserAgent) {
            if (str_contains($userAgent, strtolower($blackListedUserAgent))) {
                return true;
            }
        }

        return false;
    }
}
