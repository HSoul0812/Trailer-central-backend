<?php

namespace Tests\Unit\Services\ElasticSearch\Inventory;

use App\Exceptions\ElasticSearch\FilterNotFoundException;
use App\Models\Inventory\InventoryFilter;
use App\Services\ElasticSearch\FieldMapperServiceInterface;
use App\Services\ElasticSearch\Inventory\Builders\BooleanQueryBuilder;
use App\Services\ElasticSearch\Inventory\Builders\CustomQueryBuilder;
use App\Services\ElasticSearch\Inventory\Builders\SelectQueryBuilder;
use App\Services\ElasticSearch\Inventory\Builders\SliderQueryBuilder;
use App\Services\ElasticSearch\Inventory\FieldMapperService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test for App\Services\ElasticSearch\Inventory\FieldMapperService
 *
 * Class FieldMapperServiceTest
 * @package Tests\Unit\Services\ElasticSearch\Inventory
 *
 * @coversDefaultClass \App\Services\ElasticSearch\Inventory\FieldMapperService
 */
class FieldMapperServiceTest extends TestCase
{

    /**
     * @var FieldMapperServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();
        Cache::shouldReceive('remember')
            ->once()
            ->withAnyArgs()
            ->andReturn(collect([
                'price' => factory(InventoryFilter::class)->make([
                    'attribute' => 'price',
                    'type' => 'slider'
                ]),
                'year' => factory(InventoryFilter::class)->make([
                    'attribute' => 'year',
                    'type' => 'select'
                ]),
                'sleeping_capacity' => factory(InventoryFilter::class)->make([
                    'attribute' => 'sleeping_capacity',
                    'type' => 'select'
                ]),
                'is_special' => factory(InventoryFilter::class)->make([
                    'attribute' => 'is_special',
                    'type' => 'boolean'
                ])
            ]));
        $this->service = $this->app->make(FieldMapperService::class);
    }

    public function test_it_throws_an_exception_if_the_field_is_unknown()
    {
        $this->expectException(FilterNotFoundException::class);
        $this->service->getBuilder('some_unknown_field', 'somedata');
    }

    public function test_it_create_the_right_builder_instance_based_on_the_field_type()
    {
        $builder = $this->service->getBuilder('price', '10000:20000');
        $this->assertInstanceOf(SliderQueryBuilder::class, $builder);

        $builder = $this->service->getBuilder('year', '2020');
        $this->assertInstanceOf(SelectQueryBuilder::class, $builder);

        $builder = $this->service->getBuilder('is_special', '1');
        $this->assertInstanceOf(BooleanQueryBuilder::class, $builder);
    }

    public function test_it_resolves_the_right_builder_instance_for_known_fields_with_different_names()
    {
        $builder = $this->service->getBuilder('existingPrice', '10000:20000');
        $this->assertInstanceOf(SliderQueryBuilder::class, $builder);

        $builder = $this->service->getBuilder('numSleep', '10');
        $this->assertInstanceOf(SelectQueryBuilder::class, $builder);
    }

    public function test_it_builds_queries_for_edge_cases_with_a_custom_query_builder_instance()
    {
        $builder = $this->service->getBuilder('show_images', 'jpg;png');
        $this->assertInstanceOf(CustomQueryBuilder::class, $builder);

        $builder = $this->service->getBuilder('clearance_special', '1');
        $this->assertInstanceOf(CustomQueryBuilder::class, $builder);
    }
}
