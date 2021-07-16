<?php

namespace Tests\Unit\Services\Website;

use App\Models\Region;
use App\Models\User\DealerLocation;
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
    public function testGenerateByLocationIdWithWebsiteDealerUrl(int $locationId, MockInterface $location)
    {
        $regionName = 'test_region_name';


        $region = $this->getEloquentMock(Region::class);
        $region->region_name = $regionName;

        $location->locationRegion = $region;

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
     * @covers ::generateByLocationId
     * @dataProvider locationParamsProvider
     */
    public function testGenerateByLocationIdWithoutRegion(int $locationId, MockInterface $location)
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
            1111,
            $this->getEloquentMock(DealerLocation::class)
        ]];
    }
}
