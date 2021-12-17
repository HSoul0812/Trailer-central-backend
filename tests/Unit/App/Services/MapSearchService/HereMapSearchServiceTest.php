<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\MapSearchService;

use App\Services\MapSearchService\MapSearchServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\Common\TestCase;

class HereMapSearchServiceTest extends TestCase
{
    public function testGeocode()
    {
        $container = [];
        $this->mockHttpClient($container);
        $service = $this->getConcreteService();
        $service->geocode('test');

        $request = $container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('geocode.search.hereapi.com', $request->getUri()->getHost());
        $this->assertEquals('q=test&in=countryCode%3ACAN%2CUSA', $request->getUri()->getQuery());
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testAutocomplete()
    {
        $container = [];
        $this->mockHttpClient($container);
        $service = $this->getConcreteService();
        $service->autocomplete('test');
        $this->assertCount(1, $container);

        $request = $container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('autocomplete.search.hereapi.com', $request->getUri()->getHost());
        $this->assertEquals('q=test&in=countryCode%3ACAN%2CUSA', $request->getUri()->getQuery());
    }

    public function testReverse()
    {
        $container = [];
        $this->mockHttpClient($container);
        $service = $this->getConcreteService();
        $service->reverse(34.52, 60.52);
        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('revgeocode.search.hereapi.com', $request->getUri()->getHost());
        $this->assertEquals('at=34.52%2C60.52&lang=en-US', $request->getUri()->getQuery());
    }

    private function mockHttpClient(&$container)
    {
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new Response(200, [], '{}'),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        app()->bind('HereMapSearchService.client', function ($app) use ($stack) {
            return new Client(['handler' => $stack]);
        });
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getConcreteService()
    {
        return app()->make(MapSearchServiceInterface::class);
    }
}
