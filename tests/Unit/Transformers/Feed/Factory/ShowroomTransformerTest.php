<?php

namespace Tests\Unit\Transformers\Feed\Factory;

use App\Helpers\ConvertHelper;
use App\Models\Inventory\InventoryFeatureList;
use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomFeature;
use App\Models\Showroom\ShowroomFieldsMapping;
use App\Repositories\Showroom\ShowroomFieldsMappingRepositoryInterface;
use App\Transformers\Feed\Factory\ShowroomTransformer;
use Dingo\Api\Http\Request;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Class ShowroomTransformerTest
 * @package Tests\Unit\Transformers\Feed\Factory
 *
 * @coversDefaultClass \App\Transformers\Feed\Factory\ShowroomTransformer
 */
class ShowroomTransformerTest extends TestCase
{
    /**
     * @var LegacyMockInterface|ShowroomFieldsMappingRepositoryInterface
     */
    private $showroomFieldsMappingRepository;

    /**
     * @var LegacyMockInterface|ConvertHelper
     */
    private $convertHelper;

    /**
     * @var LegacyMockInterface|Request
     */
    private $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->showroomFieldsMappingRepository = Mockery::mock(ShowroomFieldsMappingRepositoryInterface::class);
        $this->app->instance(ShowroomFieldsMappingRepositoryInterface::class, $this->showroomFieldsMappingRepository);

        $this->convertHelper = Mockery::mock(ConvertHelper::class);
        $this->app->instance(ConvertHelper::class, $this->convertHelper);

        $this->request = Mockery::mock(Request::class);
        $this->app->instance(Request::class, $this->request);
    }

    /**
     * @covers ::transform
     */
    public function testTransform()
    {
        $field1 = 'field1';
        $field2 = 'field2';
        $field3 = 'field3';

        $mapTo1 = 'mapTo1';
        $mapTo2 = 'mapTo2';
        $mapTo3 = 'mapTo3';

        $field1Value = 'field1Value';
        $field2Value = 'field2Value';
        $fieldEmptyValue = null;

        /** @var Showroom $showroom */
        $showroom = $this->getEloquentMock(Showroom::class);
        $showroom->field1 = $field1Value;
        $showroom->field2 = $field2Value;
        $showroom->field3 = $fieldEmptyValue;

        /** @var ShowroomFieldsMapping $showroomFieldsMapping1 */
        $showroomFieldsMapping1 = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomFieldsMapping1->map_from = $field1;
        $showroomFieldsMapping1->map_to = $mapTo1;
        $showroomFieldsMapping1->type = ShowroomFieldsMapping::TYPE_INVENTORY;

        /** @var ShowroomFieldsMapping $showroomFieldsMapping2 */
        $showroomFieldsMapping2 = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomFieldsMapping2->map_from = $field2;
        $showroomFieldsMapping2->map_to = $mapTo2;
        $showroomFieldsMapping2->type = ShowroomFieldsMapping::TYPE_ATTRIBUTE;

        /** @var ShowroomFieldsMapping $showroomFieldsMappingWithEmptyValue */
        $showroomFieldsMappingWithEmptyValue = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomFieldsMappingWithEmptyValue->map_from = $field3;
        $showroomFieldsMappingWithEmptyValue->map_to = $mapTo3;
        $showroomFieldsMappingWithEmptyValue->type = ShowroomFieldsMapping::TYPE_INVENTORY;

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->once()
            ->with([])
            ->andReturn([$showroomFieldsMapping1, $showroomFieldsMapping2, $showroomFieldsMappingWithEmptyValue]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('select')
            ->andReturn(null);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('with', [])
            ->andReturn([]);

        $showroomTransformer = new ShowroomTransformer($this->showroomFieldsMappingRepository, $this->convertHelper, $this->request);
        $result = $showroomTransformer->transform($showroom);

        $this->assertIsArray($result);

        $this->assertArrayHasKey($mapTo1, $result);
        $this->assertSame($field1Value, $result[$mapTo1]);

        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey($mapTo2, $result['attributes']);
        $this->assertSame($field2Value, $result['attributes'][$mapTo2]);

        $this->assertArrayNotHasKey($mapTo3, $result);
        $this->assertArrayNotHasKey($mapTo3, $result['attributes']);
    }

    /**
     * @covers ::transform
     */
    public function testTransformWithSelect()
    {
        $field1 = 'field1';
        $field2 = 'field2';
        $field3 = 'field3';

        $mapTo1 = 'mapTo1';
        $mapTo2 = 'mapTo2';
        $mapTo3 = 'mapTo3';

        $field1Value = 'field1Value';
        $field2Value = 'field2Value';
        $notSelectedFieldValue = 'field3Value';

        /** @var Showroom $showroom */
        $showroom = $this->getEloquentMock(Showroom::class);
        $showroom->field1 = $field1Value;
        $showroom->field2 = $field2Value;
        $showroom->field3 = $notSelectedFieldValue;

        /** @var ShowroomFieldsMapping $showroomFieldsMapping1 */
        $showroomFieldsMapping1 = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomFieldsMapping1->map_from = $field1;
        $showroomFieldsMapping1->map_to = $mapTo1;
        $showroomFieldsMapping1->type = ShowroomFieldsMapping::TYPE_INVENTORY;

        /** @var ShowroomFieldsMapping $showroomFieldsMapping2 */
        $showroomFieldsMapping2 = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomFieldsMapping2->map_from = $field2;
        $showroomFieldsMapping2->map_to = $mapTo2;
        $showroomFieldsMapping2->type = ShowroomFieldsMapping::TYPE_ATTRIBUTE;

        /** @var ShowroomFieldsMapping $showroomFieldsMappingNotSelected */
        $showroomFieldsMappingNotSelected = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomFieldsMappingNotSelected->map_from = $field3;
        $showroomFieldsMappingNotSelected->map_to = $mapTo3;
        $showroomFieldsMappingNotSelected->type = ShowroomFieldsMapping::TYPE_INVENTORY;

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->once()
            ->with([])
            ->andReturn([$showroomFieldsMapping1, $showroomFieldsMapping2, $showroomFieldsMappingNotSelected]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('select')
            ->andReturn([$field1, $field2]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('with', [])
            ->andReturn([]);

        $showroomTransformer = new ShowroomTransformer($this->showroomFieldsMappingRepository, $this->convertHelper, $this->request);
        $result = $showroomTransformer->transform($showroom);

        $this->assertIsArray($result);

        $this->assertArrayHasKey($mapTo1, $result);
        $this->assertSame($field1Value, $result[$mapTo1]);

        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey($mapTo2, $result['attributes']);
        $this->assertSame($field2Value, $result['attributes'][$mapTo2]);

        $this->assertArrayNotHasKey($mapTo3, $result);
        $this->assertArrayNotHasKey($mapTo3, $result['attributes']);
    }

    /**
     * @covers ::transform
     */
    public function testTransformWithFeatures()
    {
        $feature1ListId = 123456;
        $showroomFeature1Value = 'showroomFeature1Value';

        $feature2ListId = 654321;
        $showroomFeature2Value = 'showroomFeature2Value';

        /** @var Showroom $showroom */
        $showroom = $this->getEloquentMock(Showroom::class);

        /** @var InventoryFeatureList $feature1 */
        $feature1 = $this->getEloquentMock(InventoryFeatureList::class);
        /** @var ShowroomFeature $showroomFeature1 */
        $showroomFeature1 = $this->getEloquentMock(ShowroomFeature::class);

        $feature1->feature_list_id = $feature1ListId;
        $showroomFeature1->value = $showroomFeature1Value;
        $feature1->pivot = $showroomFeature1;

        /** @var InventoryFeatureList $feature2 */
        $feature2 = $this->getEloquentMock(InventoryFeatureList::class);
        /** @var ShowroomFeature $showroomFeature2 */
        $showroomFeature2 = $this->getEloquentMock(ShowroomFeature::class);

        $feature2->feature_list_id = $feature2ListId;
        $showroomFeature2->value = $showroomFeature2Value;
        $feature2->pivot = $showroomFeature2;

        $showroom->features = [$feature1, $feature2];

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->once()
            ->with([])
            ->andReturn([]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('select')
            ->andReturn(null);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('with', [])
            ->andReturn(['features']);

        $showroomTransformer = new ShowroomTransformer($this->showroomFieldsMappingRepository, $this->convertHelper, $this->request);
        $result = $showroomTransformer->transform($showroom);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('features', $result);

        $this->assertArrayHasKey($feature1ListId, $result['features']);
        $this->assertArrayHasKey(0, $result['features'][$feature1ListId]);
        $this->assertSame($showroomFeature1Value, $result['features'][$feature1ListId][0]);

        $this->assertArrayHasKey($feature2ListId, $result['features']);
        $this->assertArrayHasKey(0, $result['features'][$feature2ListId]);
        $this->assertSame($showroomFeature2Value, $result['features'][$feature2ListId][0]);
    }

    /**
     * @covers ::transform
     */
    public function testTransformWithoutFeatures()
    {
        /** @var Showroom $showroom */
        $showroom = $this->getEloquentMock(Showroom::class);

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->once()
            ->with([])
            ->andReturn([]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('select')
            ->andReturn(null);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('with', [])
            ->andReturn([]);

        $showroomTransformer = new ShowroomTransformer($this->showroomFieldsMappingRepository, $this->convertHelper, $this->request);
        $result = $showroomTransformer->transform($showroom);

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('features', $result);
    }

    /**
     * @covers ::transform
     */
    public function testTransformWithWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $field1 = 'field1';
        $mapTo1 = 'mapTo1';
        $field1Value = 'field1Value';

        $wrongType = 'wrong_type';

        /** @var Showroom $showroom */
        $showroom = $this->getEloquentMock(Showroom::class);
        $showroom->field1 = $field1Value;

        /** @var ShowroomFieldsMapping $showroomFieldsMapping1 */
        $showroomFieldsMapping1 = $this->getEloquentMock(ShowroomFieldsMapping::class);
        $showroomFieldsMapping1->map_from = $field1;
        $showroomFieldsMapping1->map_to = $mapTo1;
        $showroomFieldsMapping1->type = $wrongType;

        $this->showroomFieldsMappingRepository
            ->shouldReceive('getAll')
            ->once()
            ->with([])
            ->andReturn([$showroomFieldsMapping1]);

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('select')
            ->andReturn(null);

        $this->request
            ->shouldReceive('get')
            ->never();

        $showroomTransformer = new ShowroomTransformer($this->showroomFieldsMappingRepository, $this->convertHelper, $this->request);
        $result = $showroomTransformer->transform($showroom);

        $this->assertIsArray($result);
    }
}
