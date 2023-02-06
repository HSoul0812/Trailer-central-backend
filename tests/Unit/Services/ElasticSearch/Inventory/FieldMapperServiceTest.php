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
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test for App\Services\ElasticSearch\Inventory\FieldMapperService
 *
 * Class FieldMapperServiceTest
 * @package Tests\Unit\Services\ElasticSearch\Inventory
 *
 * @group DW
 * @group DW_INVENTORY
 * @group DW_ELASTICSEARCH_WHICH_ARE_FAILING
 *
 * @todo This suit should be updated accordingly to past changes
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
        $field = Filter::fromArray(['name' => 'some_unknown_field', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => []
            ]
        ]]);
        $this->service->getBuilder($field);
    }

    public function test_it_create_the_right_builder_instance_based_on_the_field_type()
    {
        $field = Filter::fromArray(['name' => 'price', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => [
                    'gte' => 10000,
                    'lte' => 20000
                ]
            ]
        ]]);
        $builder = $this->service->getBuilder($field);
        $this->assertInstanceOf(SliderQueryBuilder::class, $builder);

        $field = Filter::fromArray(['name' => 'year', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => [2020]
            ]
        ]]);
        $builder = $this->service->getBuilder($field);
        $this->assertInstanceOf(SelectQueryBuilder::class, $builder);

        $field = Filter::fromArray(['name' => 'is_special', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => [true]
            ]
        ]]);
        $builder = $this->service->getBuilder($field);
        $this->assertInstanceOf(BooleanQueryBuilder::class, $builder);
    }

    public function test_it_resolves_the_right_builder_instance_for_known_fields_with_different_names()
    {
        $field = Filter::fromArray(['name' => 'existingPrice', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => [
                    'gte' => 10000,
                    'lte' => 20000
                ]
            ]
        ]]);
        $builder = $this->service->getBuilder($field);
        $this->assertInstanceOf(SliderQueryBuilder::class, $builder);

        $field = Filter::fromArray(['name' => 'numSleep', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => [
                    10
                ]
            ]
        ]]);
        $builder = $this->service->getBuilder($field);
        $this->assertInstanceOf(SelectQueryBuilder::class, $builder);
    }

    public function test_it_builds_queries_for_edge_cases_with_a_custom_query_builder_instance()
    {
        $field = Filter::fromArray(['name' => 'show_images', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => [
                    'jpg',
                    'png'
                ]
            ]
        ]]);
        $builder = $this->service->getBuilder($field);
        $this->assertInstanceOf(CustomQueryBuilder::class, $builder);

        $field = Filter::fromArray(['name' => 'clearance_special', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => []
            ]
        ]]);
        $builder = $this->service->getBuilder($field);
        $this->assertInstanceOf(CustomQueryBuilder::class, $builder);
    }
}
