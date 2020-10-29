<?php

namespace Tests\Unit\Services\Website;

use App\Repositories\Website\DealerProxyRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use App\Services\Website\WebsiteService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Test for App\Services\Website\WebsiteService
 *
 * Class WebsiteServiceTest
 * @package Tests\Unit\Services\Website
 *
 * @coversDefaultClass \App\Services\Website\WebsiteService
 */
class WebsiteServiceTest extends TestCase
{
    /**
     * @var MockInterface|WebsiteRepositoryInterface
     */
    private $websiteRepository;
    /**
     * @var MockObject|DealerProxyRepositoryInterface
     */
    private $dealerProxyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->websiteRepository = Mockery::mock(WebsiteRepositoryInterface::class);
        $this->app->instance(WebsiteRepositoryInterface::class, $this->websiteRepository);

        $this->dealerProxyRepository = $this->getMockBuilder(DealerProxyRepositoryInterface::class)
            ->onlyMethods(['create', 'update', 'delete', 'get', 'getAll'])
            ->getMock();

        $this->app->instance(DealerProxyRepositoryInterface::class, $this->dealerProxyRepository);
    }

    /**
     * @covers ::enableProxiedDomainSsl
     */
    public function testEnableProxiedDomainSsl()
    {
        $websiteId = PHP_INT_MAX;

        $domain = 'test.com';
        $secondDomain = 'www.test.com';

        $website = new \StdClass();
        $website->domain = $domain;

        $this->websiteRepository
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $websiteId])
            ->andReturn($website);

        $this->dealerProxyRepository
            ->expects($this->at(0))
            ->method('create')
            ->with($this->equalTo(['domain' => $domain, 'value' => true]))
            ->willReturn(true);

        $this->dealerProxyRepository
            ->expects($this->at(1))
            ->method('create')
            ->with($this->equalTo(['domain' => $secondDomain, 'value' => true]))
            ->willReturn(true);

        /** @var WebsiteService $service */
        $service = $this->app->make(WebsiteService::class);

        $result = $service->enableProxiedDomainSsl($websiteId);

        Log::shouldReceive('info')
            ->with('Proxied domain for SSL has been successfully enabled. Website ID - ' . $websiteId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::enableProxiedDomainSsl
     */
    public function testEnableProxiedDomainSslWithoutCreating()
    {
        $websiteId = PHP_INT_MAX;

        $domain = 'www.test.com';
        $secondDomain = 'test.com';

        $website = new \StdClass();
        $website->domain = $domain;

        $this->websiteRepository
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $websiteId])
            ->andReturn($website);

        $this->dealerProxyRepository
            ->expects($this->at(0))
            ->method('create')
            ->with($this->equalTo(['domain' => $domain, 'value' => true]))
            ->willReturn(true);

        $this->dealerProxyRepository
            ->expects($this->at(1))
            ->method('create')
            ->with($this->equalTo(['domain' => $secondDomain, 'value' => true]))
            ->willReturn(false);

        /** @var WebsiteService $service */
        $service = $this->app->make(WebsiteService::class);

        $result = $service->enableProxiedDomainSsl($websiteId);

        Log::shouldReceive('error')
            ->with('Can\'t enable proxied domain for SSL. Website ID - ' . $websiteId);

        $this->assertFalse($result);
    }

    /**
     * @covers ::enableProxiedDomainSsl
     */
    public function testEnableProxiedDomainSslWithException()
    {
        $websiteId = PHP_INT_MAX;
        $exception = new \Exception();

        $this->websiteRepository
            ->shouldReceive('get')
            ->once()
            ->with(['id' => $websiteId])
            ->andThrows($exception);

        $this->dealerProxyRepository
            ->expects($this->never())
            ->method('create');

        /** @var WebsiteService $service */
        $service = $this->app->make(WebsiteService::class);

        $result = $service->enableProxiedDomainSsl($websiteId);

        Log::shouldReceive('error')
            ->with('Enable proxied domain error. Website ID - ' . $websiteId, $exception->getTrace());

        $this->assertFalse($result);
    }
}
