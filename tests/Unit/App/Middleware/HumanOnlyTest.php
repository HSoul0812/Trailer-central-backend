<?php

namespace Tests\Unit\App\Middleware;

use App\Http\Middleware\HumanOnly;
use Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\TestCase;

class HumanOnlyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
    }

    public function testItBlocksRequestWithoutUserAgent(): void
    {
        $request = new Request();

        /** @var JsonResponse $response */
        $response = (new HumanOnly())->handle($request, function (Request $request) {
        });

        $this->assertMiddlewareReturnsEmpty($response);
    }

    public function testItAllowsRequestWithAllowedUserAgent(): void
    {
        $request = new Request();

        $request->headers->set('User-Agent', 'trailertrader-testing');

        $response = (new HumanOnly())->handle($request, fn() => true);

        $this->assertTrue($response);
    }

    public function testItAllowsRequestWithAllowedCrawlerUserAgent(): void
    {
        $request = new Request();

        $request->headers->set('User-Agent', config('crawlers.providers.yahoo.user_agents')[0]);

        $response = (new HumanOnly())->handle($request, fn() => true);

        $this->assertTrue($response);
    }

    public function testItAllowsRequestWithAllowedIpAddressFromConfig(): void
    {
        $allowedIpAddress = '123.4.5.6';

        $request = new Request();
        config(['trailertrader.middlewares.human_only.allow_ips' => $allowedIpAddress]);
        $request->server->add(['REMOTE_ADDR' => $allowedIpAddress]);

        // This test is to make sure that the allowed ip in the config works
        $response = (new HumanOnly())->handle($request, fn() => true);

        $this->assertTrue($response);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItDoesNotAllowRequestWithNotAllowedCrawlerIpAddresses()
    {
        $cacheKey = config('crawlers.providers.google.ips_cache_key');

        Cache::set($cacheKey, collect([
            '66.249.79.0/27',
        ]), 20);

        $googleBotIpAddress = '50.249.71.1';

        $request = new Request();
        $request->headers->set('User-Agent', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/W.X.Y.Z Safari/537.36');
        config(['trailertrader.middlewares.human_only.allow_ips' => '']);
        $request->server->add(['REMOTE_ADDR' => $googleBotIpAddress]);

        $response = (new HumanOnly())->handle($request, function () {
        });

        $this->assertMiddlewareReturnsEmpty($response);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testItAllowsRequestWithAllowedCrawlerIpAddresses(): void
    {
        $cacheKey = config('crawlers.providers.google.ips_cache_key');

        Cache::set($cacheKey, collect([
            '66.249.79.0/27',
        ]), 20);

        $googleBotIpAddress = '66.249.79.1';

        $request = new Request();
        $request->headers->set('User-Agent', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/W.X.Y.Z Safari/537.36');
        config(['trailertrader.middlewares.human_only.allow_ips' => '']);
        $request->server->add(['REMOTE_ADDR' => $googleBotIpAddress]);

        $response = (new HumanOnly())->handle($request, fn() => true);

        $this->assertTrue($response);
    }

    public function testItBlocksRequestFromCrawlers(): void
    {
        $request = new Request();
        $request->headers->set('User-Agent', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/W.X.Y.Z Safari/537.36');

        /** @var JsonResponse $response */
        $response = (new HumanOnly())->handle($request, function (Request $request) {
        });

        $this->assertMiddlewareReturnsEmpty($response);
    }

    private function assertMiddlewareReturnsEmpty(JsonResponse $response)
    {
        $content = $response->getOriginalContent();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEmpty($content['data']);
    }
}
