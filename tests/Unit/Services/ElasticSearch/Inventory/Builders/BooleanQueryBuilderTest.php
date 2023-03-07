<?php

namespace Tests\Unit\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Builders\BooleanQueryBuilder;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Filter;
use App\Services\ElasticSearch\Inventory\Parameters\Filters\Term;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 * @group DW_ELASTICSEARCH
 *
 * @todo This suit should be updated accordingly to past changes
 */
class BooleanQueryBuilderTest extends TestCase
{
    /** @var array */
    private $query;

    /**
     * @var array
     */
    private $expectedQuery = [
        'bool' => [
            'filter' => [
                [
                    'terms' => [
                        'is_special' => [
                            true
                        ]
                    ]
                ]
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        $field = Filter::fromArray(['name' => 'is_special', 'terms' => [
            [
                'operator' => Term::OPERATOR_EQ,
                'values' => [true]
            ]
        ]]);
        $boolQuery = new BooleanQueryBuilder($field);
        $this->query = $boolQuery->generalQuery();
    }

    public function test_it_appends_the_query_to_the_post_filters()
    {
        $this->assertArrayHasKey('post_filter', $this->query);
        $this->assertIsArray($this->query['post_filter']);
        $this->assertSame($this->expectedQuery, $this->query['post_filter']);
    }

    public function test_it_generates_aggregations_for_the_query()
    {
        $this->assertArrayHasKey('aggregations', $this->query);
        $this->assertArrayHasKey('filter_aggregations', $this->query['aggregations']);
        $this->assertArrayHasKey('selected_location_aggregations', $this->query['aggregations']);
    }

    public function test_it_appends_the_query_to_the_filter_aggregations_filter()
    {
        $this->assertArrayHasKey('filter_aggregations', $this->query['aggregations']);
        $this->assertIsArray($this->query['aggregations']['filter_aggregations']);
        $this->assertArrayHasKey('filter', $this->query['aggregations']['filter_aggregations']);
        $this->assertIsArray($this->query['aggregations']['filter_aggregations']['filter']);
        $this->assertSame($this->expectedQuery, $this->query['aggregations']['filter_aggregations']['filter']);
    }

    public function test_it_appends_the_query_to_the_selected_location_aggregations_filter()
    {
        $this->assertArrayHasKey('selected_location_aggregations', $this->query['aggregations']);
        $this->assertIsArray($this->query['aggregations']['selected_location_aggregations']);
        $this->assertArrayHasKey('filter', $this->query['aggregations']['selected_location_aggregations']);
        $this->assertIsArray($this->query['aggregations']['selected_location_aggregations']['filter']);
        $this->assertSame($this->expectedQuery, $this->query['aggregations']['selected_location_aggregations']['filter']);
    }
}
