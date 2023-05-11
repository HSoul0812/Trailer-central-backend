<?php

namespace App\Console\Commands\Crawlers;

use App\Domains\Crawlers\Strategies\CrawlerCheckStrategy;
use App\Mail\FailedToFetchBotIpEmail;
use Cache;
use Http;
use Illuminate\Console\Command;
use Mail;
use Throwable;

class CacheCrawlerIpAddressesCommand extends Command
{
    /**
     * The accept ip keys in the ip array.
     */
    public const ACCEPT_IP_KEYS = ['ipv6Prefix', 'ipv4Prefix'];

    protected $signature = 'crawlers:cache-ip-addresses';

    protected $description = 'Cache crawlers ip addresses.';

    public function handle()
    {
        collect(config('crawlers.providers'))
            ->filter(fn (array $config) => $config['strategy'] === CrawlerCheckStrategy::IP_CHECK)
            ->each(fn (array $config) => $this->createCacheFromConfig($config));
    }

    private function createCacheFromConfig(array $config): void
    {
        try {
            $ipRanges = Http::get($config['url'])
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

            Cache::forever($config['ips_cache_key'], $ipRanges);

            $this->info("{$config['provider_name']} bot ips is cached!");
        } catch (Throwable $throwable) {
            $this->error("Error when caching {$config['provider_name']} bot ips, error message: {$throwable->getMessage()}");

            if (!config('crawlers.report.cache_crawlers_ip_addresses.send_mail')) {
                return;
            }

            Mail::send(new FailedToFetchBotIpEmail(
                providerName: $config['provider_name'],
                url: $config['url'],
                errorMessage: $throwable->getMessage(),
            ));

            $sendMailTo = collect(config('crawlers.report.cache_crawlers_ip_addresses.mail_to'))->implode(', ');

            $this->error("The email is sent to $sendMailTo!");
        }
    }
}
