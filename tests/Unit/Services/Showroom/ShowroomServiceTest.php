<?php

namespace Tests\Unit\Services\Showroom;

use App\Exceptions\Showroom\ShowroomException;
use App\Models\Inventory\Attribute;
use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomFeature;
use App\Models\Showroom\ShowroomFieldsMapping;
use App\Models\Showroom\ShowroomFile;
use App\Models\Showroom\ShowroomImage;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Showroom\ShowroomFeatureRepositoryInterface;
use App\Repositories\Showroom\ShowroomFieldsMappingRepositoryInterface;
use App\Repositories\Showroom\ShowroomFileRepositoryInterface;
use App\Repositories\Showroom\ShowroomGenericMapRepositoryInterface;
use App\Repositories\Showroom\ShowroomImageRepositoryInterface;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Services\Showroom\ShowroomService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Services\Showroom\ShowroomService
 *
 * Class ShowroomServiceTest
 * @package Tests\Unit\Services\Showroom
 *
 * @coversDefaultClass \App\Services\Showroom\ShowroomService
 */
class ShowroomServiceTest extends TestCase
{
    private const INVENTORY_YEAR = 2022;
    private const INVENTORY_MANUFACTURER = 'inventory_manufacturer';
    private const INVENTORY_MODEL = 'inventory_model';
    private const INVENTORY_BRAND = 'inventory_brand';
    private const INVENTORY_ENTITY_TYPE_ID = PHP_INT_MAX - 123;

    private const INVENTORY_PULL_TYPE_FIELD = 'pull_type';
    private const INVENTORY_BRAND_FIELD = 'brand';
    private const INVENTORY_PAYLOAD_CAPACITY_FIELD = 'payload_capacity';

    private const SHOWROOM_HITCH = 'showroom_hitch';
    private const SHOWROOM_BRAND = 'showroom_brand';
    private const SHOWROOM_PAYLOAD_CAPACITY = '123';

    private const SHOWROOM_HITCH_FIELD = 'hitch';
    private const SHOWROOM_BRAND_FIELD = 'brand';
    private const SHOWROOM_PAYLOAD_CAPACITY_FIELD = 'payload_capacity';
    private const SHOWROOM_NOT_EXISTED_FIELD = 'axle_weight';

    private const SHOWROOM_FIELDS_MAPPING_TYPE_ATTRIBUTE = 'attribute';
    private const SHOWROOM_FIELDS_MAPPING_TYPE_INVENTORY = 'inventory';

    /**
     * @var ShowroomFieldsMappingRepositoryInterface|LegacyMockInterface
     */
    private $showroomFieldsMappingRepository;

    /**
     * @var ShowroomRepositoryInterface|LegacyMockInterface
     */
    private $showroomRepository;

    /**
     * @var ShowroomGenericMapRepositoryInterface|LegacyMockInterface
     */
    private $showroomGenericMapRepository;

    /**
     * @var ShowroomFeatureRepositoryInterface|LegacyMockInterface
     */
    private $showroomFeatureRepository;

    /**
     * @var ShowroomFileRepositoryInterface|LegacyMockInterface
     */
    private $showroomFileRepository;

    /**
     * @var ShowroomImageRepositoryInterface|LegacyMockInterface
     */
    private $showroomImageRepository;

    /**
     * @var AttributeRepositoryInterface|LegacyMockInterface
     */
    private $inventoryAttributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->showroomFieldsMappingRepository = Mockery::mock(ShowroomFieldsMappingRepositoryInterface::class);
        $this->app->instance(ShowroomFieldsMappingRepositoryInterface::class, $this->showroomFieldsMappingRepository);

        $this->showroomRepository = Mockery::mock(ShowroomRepositoryInterface::class);
        $this->app->instance(ShowroomRepositoryInterface::class, $this->showroomRepository);

        $this->showroomGenericMapRepository = Mockery::mock(ShowroomGenericMapRepositoryInterface::class);
        $this->app->instance(ShowroomGenericMapRepositoryInterface::class, $this->showroomGenericMapRepository);

        $this->showroomFeatureRepository = Mockery::mock(ShowroomFeatureRepositoryInterface::class);
        $this->app->instance(ShowroomFeatureRepositoryInterface::class, $this->showroomFeatureRepository);

        $this->showroomFileRepository = Mockery::mock(ShowroomFileRepositoryInterface::class);
        $this->app->instance(ShowroomFileRepositoryInterface::class, $this->showroomFileRepository);

        $this->showroomImageRepository = Mockery::mock(ShowroomImageRepositoryInterface::class);
        $this->app->instance(ShowroomImageRepositoryInterface::class, $this->showroomImageRepository);

        $this->inventoryAttributeRepository = Mockery::mock(AttributeRepositoryInterface::class);
        $this->app->instance(AttributeRepositoryInterface::class, $this->inventoryAttributeRepository);
    }

    /**
     * @covers ::mapInventoryToFactory
     *
     * @dataProvider validDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testMapInventoryToFactory(Showroom $showroom, Collection $showroomMappings, Attribute $attribute, array $unit)
    {
        $emptyCollection = new Collection([]);
        $showrooms = new Collection([$showroom]);
        $attributes = new Collection([$attribute]);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->with([])
            ->andReturn($showroomMappings)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']};{$unit['brand']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomRepository
            ->shouldReceive('getAll')
            ->with([
                'year' => $unit['year'],
                'model' => $unit['model'],
                'manufacturer' => $unit['manufacturer']
            ])
            ->andReturn($showrooms)
            ->once();

        $this->showroomFeatureRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomFileRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomImageRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->inventoryAttributeRepository
            ->shouldReceive('getAllByEntityTypeId')
            ->with($unit['entity_type_id'])
            ->andReturn($attributes)
            ->once();

        /** @var ShowroomService $showroomService */
        $showroomService = app()->make(ShowroomService::class);

        $result = $showroomService->mapInventoryToFactory($unit);

        $this->assertArrayHasKey('year', $result);
        $this->assertSame($unit['year'], $result['year']);
        $this->assertArrayHasKey('manufacturer', $result);
        $this->assertSame($unit['manufacturer'], $result['manufacturer']);
        $this->assertArrayHasKey('model', $result);
        $this->assertSame($unit['model'], $result['model']);
        $this->assertArrayHasKey('brand', $result);
        $this->assertSame($unit['brand'], $result['brand']);
        $this->assertArrayHasKey('entity_type_id', $result);
        $this->assertSame($unit['entity_type_id'], $result['entity_type_id']);
        $this->assertArrayHasKey('has_stock_images', $result);
        $this->assertFalse($result['has_stock_images']);

        $this->assertArrayHasKey('attributes', $result);
        $this->assertCount(2, $result['attributes']);
        $this->assertEquals($unit['attributes'][0], $result['attributes'][0]);
        $this->assertArrayHasKey('attribute_id', $result['attributes'][1]);
        $this->assertEquals($attribute->attribute_id, $result['attributes'][1]['attribute_id']);
        $this->assertArrayHasKey('value', $result['attributes'][1]);
        $this->assertEquals($showroom->{self::SHOWROOM_HITCH_FIELD}, $result['attributes'][1]['value']);

        $this->assertArrayHasKey('payload_capacity', $result);
        $this->assertSame($showroom->{self::SHOWROOM_PAYLOAD_CAPACITY_FIELD}, $result['payload_capacity']);

        $this->assertEmpty($result['new_images']);
        $this->assertEmpty($result['new_files']);
        $this->assertEmpty($result['features']);
    }

    /**
     * @covers ::mapInventoryToFactory
     *
     * @dataProvider validDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testMapInventoryToFactoryWithFeatures(Showroom $showroom, Collection $showroomMappings, Attribute $attribute, array $unit)
    {
        $emptyCollection = new Collection([]);
        $showrooms = new Collection([$showroom]);
        $attributes = new Collection([$attribute]);

        /** @var ShowroomFeature|LegacyMockInterface $feature */
        $feature = $this->getEloquentMock(ShowroomFeature::class);
        $feature->feature_list_id = PHP_INT_MAX - 321;
        $feature->value = 'some_value';
        $features = new Collection([$feature]);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->with([])
            ->andReturn($showroomMappings)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']};{$unit['brand']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomRepository
            ->shouldReceive('getAll')
            ->with([
                'year' => $unit['year'],
                'model' => $unit['model'],
                'manufacturer' => $unit['manufacturer']
            ])
            ->andReturn($showrooms)
            ->once();

        $this->showroomFeatureRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($features)
            ->once();

        $this->showroomFileRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomImageRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->inventoryAttributeRepository
            ->shouldReceive('getAllByEntityTypeId')
            ->with($unit['entity_type_id'])
            ->andReturn($attributes)
            ->once();

        /** @var ShowroomService $showroomService */
        $showroomService = app()->make(ShowroomService::class);

        $result = $showroomService->mapInventoryToFactory($unit);

        $this->assertArrayHasKey('year', $result);
        $this->assertSame($unit['year'], $result['year']);
        $this->assertArrayHasKey('manufacturer', $result);
        $this->assertSame($unit['manufacturer'], $result['manufacturer']);
        $this->assertArrayHasKey('model', $result);
        $this->assertSame($unit['model'], $result['model']);
        $this->assertArrayHasKey('brand', $result);
        $this->assertSame($unit['brand'], $result['brand']);
        $this->assertArrayHasKey('entity_type_id', $result);
        $this->assertSame($unit['entity_type_id'], $result['entity_type_id']);
        $this->assertArrayHasKey('has_stock_images', $result);
        $this->assertFalse($result['has_stock_images']);

        $this->assertArrayHasKey('attributes', $result);
        $this->assertCount(2, $result['attributes']);
        $this->assertEquals($unit['attributes'][0], $result['attributes'][0]);
        $this->assertArrayHasKey('attribute_id', $result['attributes'][1]);
        $this->assertEquals($attribute->attribute_id, $result['attributes'][1]['attribute_id']);
        $this->assertArrayHasKey('value', $result['attributes'][1]);
        $this->assertEquals($showroom->{self::SHOWROOM_HITCH_FIELD}, $result['attributes'][1]['value']);

        $this->assertArrayHasKey('payload_capacity', $result);
        $this->assertSame($showroom->{self::SHOWROOM_PAYLOAD_CAPACITY_FIELD}, $result['payload_capacity']);

        $this->assertEmpty($result['new_images']);
        $this->assertEmpty($result['new_files']);

        $this->assertNotEmpty($result['features']);
        $this->assertCount(1, $result['features']);
        $this->assertArrayHasKey('feature_list_id', $result['features'][0]);
        $this->assertSame($feature->feature_list_id, $result['features'][0]['feature_list_id']);
        $this->assertArrayHasKey('value', $result['features'][0]);
        $this->assertSame($feature->value, $result['features'][0]['value']);
    }

    /**
     * @covers ::mapInventoryToFactory
     *
     * @dataProvider validDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testMapInventoryToFactoryWithFiles(Showroom $showroom, Collection $showroomMappings, Attribute $attribute, array $unit)
    {
        $emptyCollection = new Collection([]);
        $showrooms = new Collection([$showroom]);
        $attributes = new Collection([$attribute]);

        /** @var ShowroomFile|LegacyMockInterface $showroomFile */
        $showroomFile = $this->getEloquentMock(ShowroomFile::class);
        $showroomFile->src = 'some_showroom_file_src';
        $showroomFile->name = 'some_showroom_file_name';
        $showroomFiles = new Collection([$showroomFile]);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->with([])
            ->andReturn($showroomMappings)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']};{$unit['brand']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomRepository
            ->shouldReceive('getAll')
            ->with([
                'year' => $unit['year'],
                'model' => $unit['model'],
                'manufacturer' => $unit['manufacturer']
            ])
            ->andReturn($showrooms)
            ->once();

        $this->showroomFeatureRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomFileRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($showroomFiles)
            ->once();

        $this->showroomImageRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->inventoryAttributeRepository
            ->shouldReceive('getAllByEntityTypeId')
            ->with($unit['entity_type_id'])
            ->andReturn($attributes)
            ->once();

        /** @var ShowroomService $showroomService */
        $showroomService = app()->make(ShowroomService::class);

        $result = $showroomService->mapInventoryToFactory($unit);

        $this->assertArrayHasKey('year', $result);
        $this->assertSame($unit['year'], $result['year']);
        $this->assertArrayHasKey('manufacturer', $result);
        $this->assertSame($unit['manufacturer'], $result['manufacturer']);
        $this->assertArrayHasKey('model', $result);
        $this->assertSame($unit['model'], $result['model']);
        $this->assertArrayHasKey('brand', $result);
        $this->assertSame($unit['brand'], $result['brand']);
        $this->assertArrayHasKey('entity_type_id', $result);
        $this->assertSame($unit['entity_type_id'], $result['entity_type_id']);
        $this->assertArrayHasKey('has_stock_images', $result);
        $this->assertFalse($result['has_stock_images']);

        $this->assertArrayHasKey('attributes', $result);
        $this->assertCount(2, $result['attributes']);
        $this->assertEquals($unit['attributes'][0], $result['attributes'][0]);
        $this->assertArrayHasKey('attribute_id', $result['attributes'][1]);
        $this->assertEquals($attribute->attribute_id, $result['attributes'][1]['attribute_id']);
        $this->assertArrayHasKey('value', $result['attributes'][1]);
        $this->assertEquals($showroom->{self::SHOWROOM_HITCH_FIELD}, $result['attributes'][1]['value']);

        $this->assertArrayHasKey('payload_capacity', $result);
        $this->assertSame($showroom->{self::SHOWROOM_PAYLOAD_CAPACITY_FIELD}, $result['payload_capacity']);

        $this->assertEmpty($result['new_images']);
        $this->assertEmpty($result['features']);

        $this->assertNotEmpty($result['new_files']);
        $this->assertCount(1, $result['new_files']);
        $this->assertArrayHasKey('title', $result['new_files'][0]);
        $this->assertSame('/showroom-files/' . $showroomFile->src, $result['new_files'][0]['title']);
        $this->assertArrayHasKey('url', $result['new_files'][0]);
        $this->assertNotFalse(strpos($result['new_files'][0]['url'], '/showroom-files/' . $showroomFile->src));
        $this->assertArrayHasKey('is_active', $result['new_files'][0]);
        $this->assertSame(1, $result['new_files'][0]['is_active']);
    }

    /**
     * @covers ::mapInventoryToFactory
     *
     * @dataProvider validDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testMapInventoryToFactoryWithImages(Showroom $showroom, Collection $showroomMappings, Attribute $attribute, array $unit)
    {
        $emptyCollection = new Collection([]);
        $showrooms = new Collection([$showroom]);
        $attributes = new Collection([$attribute]);

        /** @var ShowroomImage|LegacyMockInterface $showroomImage1 */
        $showroomImage1 = $this->getEloquentMock(ShowroomImage::class);
        $showroomImage1->src = 'some_showroom_image_src_1';
        $showroomImage1->has_stock_overlay = false;
        $showroomImage1->is_floorplan = false;

        /** @var ShowroomImage|LegacyMockInterface $showroomImage2 */
        $showroomImage2 = $this->getEloquentMock(ShowroomImage::class);
        $showroomImage2->src = 'some_showroom_image_src_2';
        $showroomImage2->has_stock_overlay = true;
        $showroomImage2->is_floorplan = true;

        $showroomImages = new Collection([$showroomImage1, $showroomImage2]);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->with([])
            ->andReturn($showroomMappings)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']};{$unit['brand']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomRepository
            ->shouldReceive('getAll')
            ->with([
                'year' => $unit['year'],
                'model' => $unit['model'],
                'manufacturer' => $unit['manufacturer']
            ])
            ->andReturn($showrooms)
            ->once();

        $this->showroomFeatureRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomFileRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomImageRepository
            ->shouldReceive('getAll')
            ->with(['showroom_id' => $showroom->id])
            ->andReturn($showroomImages)
            ->once();

        $this->inventoryAttributeRepository
            ->shouldReceive('getAllByEntityTypeId')
            ->with($unit['entity_type_id'])
            ->andReturn($attributes)
            ->once();

        /** @var ShowroomService $showroomService */
        $showroomService = app()->make(ShowroomService::class);

        $result = $showroomService->mapInventoryToFactory($unit);

        $this->assertArrayHasKey('year', $result);
        $this->assertSame($unit['year'], $result['year']);
        $this->assertArrayHasKey('manufacturer', $result);
        $this->assertSame($unit['manufacturer'], $result['manufacturer']);
        $this->assertArrayHasKey('model', $result);
        $this->assertSame($unit['model'], $result['model']);
        $this->assertArrayHasKey('brand', $result);
        $this->assertSame($unit['brand'], $result['brand']);
        $this->assertArrayHasKey('entity_type_id', $result);
        $this->assertSame($unit['entity_type_id'], $result['entity_type_id']);
        $this->assertArrayHasKey('has_stock_images', $result);
        $this->assertTrue($result['has_stock_images']);

        $this->assertArrayHasKey('attributes', $result);
        $this->assertCount(2, $result['attributes']);
        $this->assertEquals($unit['attributes'][0], $result['attributes'][0]);
        $this->assertArrayHasKey('attribute_id', $result['attributes'][1]);
        $this->assertEquals($attribute->attribute_id, $result['attributes'][1]['attribute_id']);
        $this->assertArrayHasKey('value', $result['attributes'][1]);
        $this->assertEquals($showroom->{self::SHOWROOM_HITCH_FIELD}, $result['attributes'][1]['value']);

        $this->assertArrayHasKey('payload_capacity', $result);
        $this->assertSame($showroom->{self::SHOWROOM_PAYLOAD_CAPACITY_FIELD}, $result['payload_capacity']);

        $this->assertEmpty($result['new_files']);
        $this->assertEmpty($result['features']);

        $this->assertNotEmpty($result['new_images']);
        $this->assertCount(2, $result['new_images']);

        $this->assertArrayHasKey('url', $result['new_images'][0]);
        $this->assertSame(config('app.cdn_url') . '/showroom-files/' . $showroomImage1->src, $result['new_images'][0]['url']);
        $this->assertArrayHasKey('is_stock', $result['new_images'][0]);
        $this->assertFalse($result['new_images'][0]['is_stock']);
        $this->assertArrayHasKey('position', $result['new_images'][0]);
        $this->assertSame(1, $result['new_images'][0]['position']);

        $this->assertArrayHasKey('url', $result['new_images'][1]);
        $this->assertSame(config('app.cdn_url') . '/showroom-files/' . $showroomImage2->src, $result['new_images'][1]['url']);
        $this->assertArrayHasKey('is_stock', $result['new_images'][1]);
        $this->assertTrue($result['new_images'][1]['is_stock']);
        $this->assertArrayHasKey('position', $result['new_images'][1]);
        $this->assertSame(2, $result['new_images'][1]['position']);
        $this->assertArrayHasKey('is_secondary', $result['new_images'][1]);
        $this->assertTrue($result['new_images'][1]['is_secondary']);
    }

    /**
     * @covers ::mapInventoryToFactory
     *
     * @dataProvider unitDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testMapInventoryToFactoryWithoutShowroom(array $unit)
    {
        $emptyCollection = new Collection([]);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']};{$unit['brand']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomRepository
            ->shouldReceive('getAll')
            ->with([
                'year' => $unit['year'],
                'model' => $unit['model'],
                'manufacturer' => $unit['manufacturer']
            ])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomFeatureRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomFileRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomImageRepository
            ->shouldReceive('getAll')
            ->never();

        $this->inventoryAttributeRepository
            ->shouldReceive('getAllByEntityTypeId')
            ->never();

        /** @var ShowroomService $showroomService */
        $showroomService = app()->make(ShowroomService::class);

        $result = $showroomService->mapInventoryToFactory($unit);

        $this->assertSame($unit, $result);
    }

    /**
     * @covers ::mapInventoryToFactory
     *
     * @dataProvider unitDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testMapInventoryToFactoryWithoutShowroomWithoutBrand(array $unit)
    {
        unset($unit['brand']);

        $emptyCollection = new Collection([]);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->with(['external_mfg_key' => "{$unit['year']};{$unit['manufacturer']};{$unit['model']}"])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomRepository
            ->shouldReceive('getAll')
            ->with([
                'year' => $unit['year'],
                'model' => $unit['model'],
                'manufacturer' => $unit['manufacturer']
            ])
            ->andReturn($emptyCollection)
            ->once();

        $this->showroomFeatureRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomFileRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomImageRepository
            ->shouldReceive('getAll')
            ->never();

        $this->inventoryAttributeRepository
            ->shouldReceive('getAllByEntityTypeId')
            ->never();

        /** @var ShowroomService $showroomService */
        $showroomService = app()->make(ShowroomService::class);

        $result = $showroomService->mapInventoryToFactory($unit);

        $this->assertSame($unit, $result);
    }

    /**
     * @covers ::mapInventoryToFactory
     *
     * @dataProvider notValidDataProvider
     *
     * @group INTEGRATIONS
     * @group INTEGRATIONS_SHOWROOM
     */
    public function testMapInventoryToFactoryWithoutRequiredParams(array $unit)
    {
        $this->expectException(ShowroomException::class);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomGenericMapRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomFeatureRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomFileRepository
            ->shouldReceive('getAll')
            ->never();

        $this->showroomImageRepository
            ->shouldReceive('getAll')
            ->never();

        $this->inventoryAttributeRepository
            ->shouldReceive('getAllByEntityTypeId')
            ->never();

        /** @var ShowroomService $showroomService */
        $showroomService = app()->make(ShowroomService::class);

        $showroomService->mapInventoryToFactory($unit);
    }

    /**
     * @return \array[][]
     */
    public function notValidDataProvider(): array
    {
        return [
            ['Without Model' => [
                'year' => self::INVENTORY_YEAR,
                'manufacturer' => self::INVENTORY_MANUFACTURER,
                'brand' => self::INVENTORY_BRAND,
                'entity_type_id' => self::INVENTORY_ENTITY_TYPE_ID,
                'attributes' => [
                    [
                        'attribute_id' => PHP_INT_MAX - 1,
                        'value' => 'some_attribute',
                    ]
                ]
            ]],
            ['Without Manufacturer' => [
                'year' => self::INVENTORY_YEAR,
                'model' => self::INVENTORY_MODEL,
                'brand' => self::INVENTORY_BRAND,
                'entity_type_id' => self::INVENTORY_ENTITY_TYPE_ID,
                'attributes' => [
                    [
                        'attribute_id' => PHP_INT_MAX - 1,
                        'value' => 'some_attribute',
                    ]
                ]
            ]],
            ['Without Year' => [
                'model' => self::INVENTORY_MODEL,
                'manufacturer' => self::INVENTORY_MANUFACTURER,
                'brand' => self::INVENTORY_BRAND,
                'entity_type_id' => self::INVENTORY_ENTITY_TYPE_ID,
                'attributes' => [
                    [
                        'attribute_id' => PHP_INT_MAX - 1,
                        'value' => 'some_attribute',
                    ]
                ]
            ]],
        ];
    }

    /**
     * @return array[]
     */
    public function validDataProvider(): array
    {
        $showroom = $this->getEloquentMock(Showroom::class);

        $showroom->{self::SHOWROOM_HITCH_FIELD} = self::SHOWROOM_HITCH;
        $showroom->{self::SHOWROOM_BRAND_FIELD} = self::SHOWROOM_BRAND;
        $showroom->{self::SHOWROOM_PAYLOAD_CAPACITY_FIELD} = self::SHOWROOM_PAYLOAD_CAPACITY;
        $showroom->{self::SHOWROOM_NOT_EXISTED_FIELD} = PHP_INT_MAX;

        $showroomMapping1 = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomMapping2 = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomMapping3 = $this->getEloquentMock(ShowroomFieldsMapping::class);

        $showroomMapping1->map_from = self::SHOWROOM_HITCH_FIELD;
        $showroomMapping1->map_to = self::INVENTORY_PULL_TYPE_FIELD;
        $showroomMapping1->type = self::SHOWROOM_FIELDS_MAPPING_TYPE_ATTRIBUTE;

        $showroomMapping2->map_from = self::SHOWROOM_BRAND_FIELD;
        $showroomMapping2->map_to = self::INVENTORY_BRAND_FIELD;
        $showroomMapping2->type = self::SHOWROOM_FIELDS_MAPPING_TYPE_INVENTORY;

        $showroomMapping3->map_from = self::SHOWROOM_PAYLOAD_CAPACITY_FIELD;
        $showroomMapping3->map_to = self::INVENTORY_PAYLOAD_CAPACITY_FIELD;
        $showroomMapping3->type = self::SHOWROOM_FIELDS_MAPPING_TYPE_INVENTORY;

        $showroomMappings = new Collection([$showroomMapping1, $showroomMapping2, $showroomMapping3]);

        $unit = $this->unitDataProvider()[0][0];

        $attribute = $this->getEloquentMock(Attribute::class);

        $attribute->attribute_id = PHP_INT_MAX - 2;
        $attribute->code = self::INVENTORY_PULL_TYPE_FIELD;

        return [
            [
                $showroom,
                $showroomMappings,
                $attribute,
                $unit
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function unitDataProvider(): array
    {
        return [
            [
                [
                    'year' => self::INVENTORY_YEAR,
                    'manufacturer' => self::INVENTORY_MANUFACTURER,
                    'model' => self::INVENTORY_MODEL,
                    'brand' => self::INVENTORY_BRAND,
                    'entity_type_id' => self::INVENTORY_ENTITY_TYPE_ID,
                    'attributes' => [
                        [
                            'attribute_id' => PHP_INT_MAX - 1,
                            'value' => 'some_attribute',
                        ]
                    ]
                ]
            ]
        ];
    }
}
