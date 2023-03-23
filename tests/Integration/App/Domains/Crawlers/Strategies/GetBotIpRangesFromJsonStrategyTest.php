<?php

namespace Tests\Integration\App\Domains\Crawlers\Strategies;

use App\Domains\Crawlers\Strategies\GetBotIpRangesFromJsonStrategy;
use App\Http\Middleware\HumanOnly;
use App\Mail\FailedToFetchBotIpEmail;
use Mail;
use Tests\Common\TestCase;

class GetBotIpRangesFromJsonStrategyTest extends TestCase
{
    public function testItCanFetchBotIpAddresses()
    {
        Mail::fake();

        foreach (HumanOnly::BOT_IPS_FETCHER_CONFIGS as $config) {
            $strategy = new GetBotIpRangesFromJsonStrategy(
                $config['provider_name'],
                $config['url'],
            );

            $ipRanges = $strategy->getIpRanges();

            Mail::assertNotQueued(FailedToFetchBotIpEmail::class);

            $this->assertNotEmpty($ipRanges);
        }
    }
}
