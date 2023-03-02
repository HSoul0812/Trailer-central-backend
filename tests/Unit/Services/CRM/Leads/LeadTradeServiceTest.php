<?php

namespace Tests\Unit\Services\CRM\Leads;

use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;
use App\Models\CRM\Leads\LeadTrade;
use App\Models\CRM\Leads\LeadTradeImage;
use Illuminate\Support\Facades\Storage;
use App\Repositories\CRM\Leads\LeadTradeRepositoryInterface;
use App\Services\CRM\Leads\LeadTradeService;
use App\Services\CRM\Leads\LeadTradeServiceInterface;
use Illuminate\Http\UploadedFile;
use Faker\Factory as Faker;
use App\Helpers\ImageHelper;

class LeadTradeServiceTest extends TestCase
{
    const TEST_VEHICLE_MAKE = 'Tesla';
    const TEST_VEHICLE_MODEL = 'Model S';
    const TEST_VEHICLE_YEAR = '2023';
    const TEST_NOTE = 'this is a note';

    const TEST_LEAD_ID = 1;
    const TEST_TRADE_ID = 1;

    /**
     * @var LegacyMockInterface|LeadTradeRepositoryInterface
     */
    protected $leadTradeRepositoryMock;

    /**
     * @var LegacyMockInterface|ImageHelper
     */
    protected $imageHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('leadTradeRepositoryMock', LeadTradeRepositoryInterface::class);

        $this->instanceMock('imageHelper', ImageHelper::class);

        $this->faker = Faker::create();

        Storage::fake('s3');
    }

    public function tearDown(): void
    {
        Storage::fake('s3');

        parent::tearDown();
    }

    /**
     * @group CRM
     */
    public function testCreate()
    {
        $createParams = [
            'make' => self::TEST_VEHICLE_MAKE,
            'model' => self::TEST_VEHICLE_MODEL,
            'year' => self::TEST_VEHICLE_YEAR,
            'notes' => self::TEST_NOTE,
            'images' => [
                UploadedFile::fake()->create('image.png')
            ]
        ];

        $trade = $this->getEloquentMock(LeadTrade::class);
        $trade->id = self::TEST_TRADE_ID;
        $trade->shouldReceive('setRelation')->passthru();

        // mock getRandomString()
        $randomString = $this->faker->md5();
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->once()
            ->andReturn($randomString);

        $this->leadTradeRepositoryMock->shouldReceive('create')
            ->once()->with($createParams)->andReturn($trade);

        $this->leadTradeRepositoryMock->shouldReceive('createImage')
            ->once()->with([
                'trade_id' => self::TEST_TRADE_ID,
                'filename' => 'image.png',
                'path' => Storage::disk('s3')->url($randomString)
            ])->andReturn($this->getEloquentMock(LeadTradeImage::class));

        $service = $this->app->make(LeadTradeService::class);
        $service->create($createParams);
    }

    /**
     * @group CRM
     */
    public function testUpdate()
    {
        $tradeImageId1 = 1;
        $tradeImageId2 = 2;
        $tradeImageIds = [$tradeImageId1, $tradeImageId2];

        $updateParams = [
            'id' => self::TEST_TRADE_ID,
            'make' => self::TEST_VEHICLE_MAKE,
            'model' => self::TEST_VEHICLE_MODEL,
            'year' => self::TEST_VEHICLE_YEAR,
            'notes' => self::TEST_NOTE,
            'new_images' => [
                UploadedFile::fake()->create('image.png')
            ],
            'existing_images' => [
                [
                    'id' => $tradeImageId2
                ]
            ]
        ];

        // mock getRandomString()
        $randomString = $this->faker->md5();
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->once()
            ->andReturn($randomString);

        $trade = $this->getEloquentMock(LeadTrade::class);
        $trade->id = self::TEST_TRADE_ID;
        $trade->shouldReceive('setRelation')->passthru();

        $this->leadTradeRepositoryMock->shouldReceive('update')
            ->once()->with($updateParams)->andReturn($trade);

        $this->leadTradeRepositoryMock->shouldReceive('getImageIds')
            ->once()->with(self::TEST_TRADE_ID)->andReturn($tradeImageIds);

        $this->leadTradeRepositoryMock->shouldReceive('createImage')
            ->once()->with([
                'trade_id' => self::TEST_TRADE_ID,
                'filename' => 'image.png',
                'path' => Storage::disk('s3')->url($randomString)
            ])->andReturn($this->getEloquentMock(LeadTradeImage::class));

        $this->leadTradeRepositoryMock->shouldReceive('deleteImage')
            ->once()->with($tradeImageId1)->andReturn(true);

        $this->leadTradeRepositoryMock->shouldReceive('getImagePath')
            ->once()->with($tradeImageId1);

        $this->leadTradeRepositoryMock->shouldReceive('getImages')
            ->once()->with(self::TEST_TRADE_ID)
            ->andReturn(collect([$this->getEloquentMock(LeadTradeImage::class)]));

        $service = $this->app->make(LeadTradeService::class);
        $service->update($updateParams);
    }
}