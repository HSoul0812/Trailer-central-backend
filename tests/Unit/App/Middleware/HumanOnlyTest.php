<?php

namespace Tests\Unit\App\Middleware;

use App\Http\Middleware\HumanOnly;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\TestCase;

class HumanOnlyTest extends TestCase
{
    public function testItBlocksRequestWithoutUserAgent()
    {
        $request = new Request();

        /** @var JsonResponse $response */
        $response = (new HumanOnly())->handle($request, function (Request $request) {
        });

        $this->assertMiddlewareReturnsEmpty($response);
    }

    public function testItAllowsRequestWithAllowedUserAgent()
    {
        $request = new Request();
        $request->headers->set('User-Agent', 'trailertrader-testing');

        (new HumanOnly())->handle($request, function () {
            $this->assertTrue(true);
        });
    }

    public function testItAllowsRequestWithAllowedIpAddress()
    {
        // @see https://developers.google.com/static/search/apis/ipranges/googlebot.json
        $googleBotIpAddress = '66.249.79.1';

        $fakeAllowedIpAddress = '123.4.5.6';

        $request = new Request();
        config(['trailertrader.middlewares.human_only.allow_ips' => $fakeAllowedIpAddress]);
        $request->server->add(['REMOTE_ADDR' => $fakeAllowedIpAddress]);

        $fakeGoogleBotResponseArray = [
            'prefixes' => [[
                'ipv4Prefix' => '66.249.79.0/27',
            ]],
        ];

        $httpFakeResponses = Http::sequence()
            ->push($fakeGoogleBotResponseArray)
            ->push($fakeGoogleBotResponseArray);

        Http::fake([
            'developers.google.com/*' => $httpFakeResponses,
        ]);

        // This test is to make sure that the allowed ip in the config works
        (new HumanOnly())->handle($request, function () {
            $this->assertTrue(true);
        });

        // This is to make sure that the allowed GoogleBot IP addresses works
        $request->headers->set('User-Agent', 'trailertrader-testing');
        config(['trailertrader.middlewares.human_only.allow_ips' => '']);
        $request->server->add(['REMOTE_ADDR' => $googleBotIpAddress]);

        (new HumanOnly())->handle($request, function () {
            $this->assertTrue(true);
        });
    }

    public function testItBlocksRequestFromCrawlers()
    {
        $fakeGoogleBotResponseArray = [
            'prefixes' => [[
                'ipv4Prefix' => '66.249.79.0/27',
            ]],
        ];

        $httpFakeResponses = Http::sequence()->push($fakeGoogleBotResponseArray);

        Http::fake([
            'developers.google.com/*' => $httpFakeResponses,
        ]);

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
