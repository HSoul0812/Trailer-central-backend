<?php

namespace Tests\Unit\Services\Website;

use App\Models\Region;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Models\Website\WebsiteDealerUrl;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\Website\WebsiteDealerUrlRepositoryInterface;
use App\Services\Website\WebsiteDealerUrlService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Class WebsiteDealerUrlServiceTest
 * @package Tests\Unit\Services\Website
 *
 * @coversDefaultClass \App\Services\Website\WebsiteDealerUrlService
 */
class WebsiteDealerUrlServiceTest extends TestCase
{
    /**
     * @var MockInterface|DealerLocationRepositoryInterface
     */
    private $dealerLocationRepository;
    /**
     * @var MockObject|WebsiteDealerUrlRepositoryInterface
     */
    private $websiteDealerUrlRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->dealerLocationRepository = Mockery::mock(DealerLocationRepositoryInterface::class);
        $this->app->instance(DealerLocationRepositoryInterface::class, $this->dealerLocationRepository);

        $this->websiteDealerUrlRepository = Mockery::mock(WebsiteDealerUrlRepositoryInterface::class);
        $this->app->instance(WebsiteDealerUrlRepositoryInterface::class, $this->websiteDealerUrlRepository);
    }

    /**
     * @covers ::generateByLocationId
     * @dataProvider locationParamsProvider
     */
    public function testGenerateByLocationIdWithoutWebsiteDealerUrl(int $dealerId, int $locationId, MockInterface $location)
    {
        $region = $this->getEloquentMock(Region::class);
        $region->region_name = 'test_region_name';

        $dealer = $this->getEloquentMock(User::class);
        $dealer->name = 'test_name';

        $location->dealer_id = $dealerId;
        $location->locationRegion = $region;
        $location->user = $dealer;

        $this->dealerLocationRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_location_id' => $locationId])
            ->andReturn($location);

        $this->websiteDealerUrlRepository
            ->shouldReceive('exists')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($locationId) {
                return isset($arg['not_location_id'])
                    && $arg['not_location_id'] === $locationId
                    && isset($arg['url'])
                    && stripos($arg['url'], '/trailer-dealer-in-') === 0;
            }))
            ->andReturn(false);

        $this->websiteDealerUrlRepository
            ->shouldReceive('exists')
            ->once()
            ->with(['location_id' => $locationId])
            ->andReturn(false);

        $this->websiteDealerUrlRepository
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($arg) use ($locationId, $dealerId) {
                return isset($arg['location_id'])
                    && $arg['location_id'] === $locationId
                    && isset($arg['dealer_id'])
                    && $arg['dealer_id'] === $dealerId
                    && isset($arg['url'])
                    && stripos($arg['url'], '/trailer-dealer-in-') === 0;
            }))
            ->once()
            ->andReturn($this->getEloquentMock(WebsiteDealerUrl::class));

        $this->websiteDealerUrlRepository
            ->shouldReceive('update')
            ->never();

        /** @var WebsiteDealerUrlService $service */
        $service = $this->app->make(WebsiteDealerUrlService::class);
        $result = $service->generateByLocationId($locationId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::generateByLocationId
     * @dataProvider locationParamsProvider
     */
    public function testGenerateByLocationIdWithWebsiteDealerUrl(int $dealerId, int $locationId, MockInterface $location)
    {
        $region = $this->getEloquentMock(Region::class);
        $region->region_name = 'test_region_name';

        $dealer = $this->getEloquentMock(User::class);
        $dealer->name = 'test_name';

        $location->dealer_id = $dealerId;
        $location->locationRegion = $region;
        $location->user = $dealer;

        $this->dealerLocationRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_location_id' => $locationId])
            ->andReturn($location);

        $this->websiteDealerUrlRepository
            ->shouldReceive('exists')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($locationId) {
                return isset($arg['not_location_id'])
                    && $arg['not_location_id'] === $locationId
                    && isset($arg['url'])
                    && stripos($arg['url'], '/trailer-dealer-in-') === 0;
            }))
            ->andReturn(false);

        $this->websiteDealerUrlRepository
            ->shouldReceive('exists')
            ->once()
            ->with(['location_id' => $locationId])
            ->andReturn(true);

        $this->websiteDealerUrlRepository
            ->shouldReceive('update')
            ->with(\Mockery::on(function ($arg) use ($locationId, $dealerId) {
                return isset($arg['location_id'])
                    && $arg['location_id'] === $locationId
                    && isset($arg['dealer_id'])
                    && $arg['dealer_id'] === $dealerId
                    && isset($arg['url'])
                    && stripos($arg['url'], '/trailer-dealer-in-') === 0;
            }))
            ->once()
            ->andReturn($this->getEloquentMock(WebsiteDealerUrl::class));

        $this->websiteDealerUrlRepository
            ->shouldReceive('create')
            ->never();

        /** @var WebsiteDealerUrlService $service */
        $service = $this->app->make(WebsiteDealerUrlService::class);
        $result = $service->generateByLocationId($locationId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::generateByLocationId
     * @dataProvider locationParamsProvider
     */
    public function testGenerateByLocationIdWithoutRegion(int $dealerId, int $locationId, MockInterface $location)
    {
        $location->locationRegion = null;

        $this->dealerLocationRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_location_id' => $locationId])
            ->andReturn($location);

        $this->websiteDealerUrlRepository
            ->shouldReceive('create')
            ->never();

        $this->websiteDealerUrlRepository
            ->shouldReceive('update')
            ->never();

        /** @var WebsiteDealerUrlService $service */
        $service = $this->app->make(WebsiteDealerUrlService::class);
        $result = $service->generateByLocationId($locationId);

        $this->assertFalse($result);
    }

    /**
     * @return string[][][]
     */
    public function locationParamsProvider(): array
    {
        return [[
            99999,
            1111,
            $this->getEloquentMock(DealerLocation::class)
        ]];
    }
}
