<?php

namespace Tests\Integration\App\Console\Commands;

use App\Console\Commands\Crawlers\CacheCrawlerIpAddressesCommand;
use App\Domains\Crawlers\Strategies\CrawlerCheckStrategy;
use App\Mail\FailedToFetchBotIpEmail;
use Cache;
use Http;
use Illuminate\Support\Collection;
use Mail;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\TestCase;

class CacheCrawlerIpAddressesCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
    }

    public function testItCanCacheCrawlerIpAddresses()
    {
        $providers = collect(config('crawlers.providers'))
            ->filter(fn (array $config) => $config['strategy'] === CrawlerCheckStrategy::IP_CHECK);

        $providers->each(function (array $config) {
            $this->assertNull(Cache::get($config['ips_cache_key']));
        });

        $this->artisan(CacheCrawlerIpAddressesCommand::class);

        $providers->each(function (array $config) {
            $value = Cache::get($config['ips_cache_key']);
            $this->assertInstanceOf(Collection::class, $value);
            $this->assertNotEmpty($value);
        });
    }

    public function testItCanSendsEmailWhenThereIsAnError()
    {
        $fakeResponseSequence = Http::sequence()
            ->push('error', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->push('error', Response::HTTP_INTERNAL_SERVER_ERROR);

        Http::fake([
            '*' => $fakeResponseSequence,
        ]);

        Mail::fake();

        config([
            'crawlers.report.cache_crawlers_ip_addresses.send_mail' => true,
        ]);

        $this->artisan(CacheCrawlerIpAddressesCommand::class);

        Mail::assertSent(FailedToFetchBotIpEmail::class);
    }

    public function testItWillNotSendEmailWhenThereIsAnErrorIfConfigSaidSo()
    {
        $fakeResponseSequence = Http::sequence()
            ->push('error', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->push('error', Response::HTTP_INTERNAL_SERVER_ERROR);

        Http::fake([
            '*' => $fakeResponseSequence,
        ]);

        Mail::fake();

        config([
            'crawlers.report.cache_crawlers_ip_addresses.send_mail' => false,
        ]);

        $this->artisan(CacheCrawlerIpAddressesCommand::class);

        Mail::assertNothingSent();
    }
}
