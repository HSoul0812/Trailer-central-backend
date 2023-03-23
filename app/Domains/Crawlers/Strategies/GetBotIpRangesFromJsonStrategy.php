<?php

namespace App\Domains\Crawlers\Strategies;

use App\Mail\FailedToFetchBotIpEmail;
use Cache;
use Http;
use Illuminate\Support\Collection;
use Mail;
use Psr\SimpleCache\InvalidArgumentException;
use Str;
use Stringable;
use Throwable;

/**
 * This class is for extracting ip ranges from the JSON file provided
 * by some popular provider such as Google and Bing
 */
class GetBotIpRangesFromJsonStrategy
{
    /**
     * The amount of time before the cache will be invalidated
     */
    const CACHE_SECONDS = 86_400;

    /**
     * We also store the failed fetch in the cache, so we give
     * some time for the provider endpoint to recover before we
     * try to send the next request to it
     */
    const FETCH_FAILED_CACHE_SECONDS = 60;

    /**
     * The accept ip keys in the ip array
     */
    const ACCEPT_IP_KEYS = ['ipv6Prefix', 'ipv4Prefix'];

    public function __construct(
        private string $providerName,
        private string $url,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getIpRanges(): Collection
    {
        $cacheKey = (string) $this->getCalculatedCacheKey();

        if ($cachedIpRanges = Cache::get($cacheKey)) {
            return $cachedIpRanges;
        }

        try {
            $ipRanges = Http::get($this->url)
                ->throw()
                ->collect('prefixes')
                ->map(function (array $response) {
                    foreach (self::ACCEPT_IP_KEYS as $acceptKey) {
                        if (array_key_exists($acceptKey, $response)) {
                            return $response[$acceptKey];
                        }
                    }

                    return null;
                })
                ->filter()
                ->values();

            Cache::set($cacheKey, $ipRanges, self::CACHE_SECONDS);

            return $ipRanges;
        } catch (Throwable $throwable) {
            $this->sendFailedToFetchBotIpAddressesEmail(
                providerName: $this->providerName,
                url: $this->url,
                throwable: $throwable,
            );

            $ipRanges = collect([]);

            Cache::set($cacheKey, $ipRanges, self::FETCH_FAILED_CACHE_SECONDS);

            return $ipRanges;
        }
    }

    /**
     * Get the calculated cache key
     *
     * @return Stringable
     */
    private function getCalculatedCacheKey(): Stringable
    {
        return Str::of($this->providerName)->lower()->append('-bot-ips');
    }

    private function sendFailedToFetchBotIpAddressesEmail(string $providerName, string $url, Throwable $throwable): void
    {
        if (!config('trailertrader.middlewares.human_only.emails.failed_bot_ips_fetch.send_mail')) {
            return;
        }

        Mail::queue(new FailedToFetchBotIpEmail(
            providerName: $providerName,
            url: $url,
            errorMessage: $throwable->getMessage(),
        ));
    }
}
