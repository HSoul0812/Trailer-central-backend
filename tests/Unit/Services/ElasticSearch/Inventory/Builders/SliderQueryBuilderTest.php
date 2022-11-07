<?php

namespace Tests\Unit\Services\ElasticSearch\Inventory\Builders;

use App\Services\ElasticSearch\Inventory\Builders\SliderQueryBuilder;
use Tests\TestCase;

class SliderQueryBuilderTest extends TestCase
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
                    'range' => [
                        'existingPrice' => [
                            'gte' => '1000',
                            'lte' => '2000'
                        ]
                    ]
                ]
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        $sliderQuery = new SliderQueryBuilder('existingPrice', '1000:2000');
        $this->query = $sliderQuery->query();
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
        $this->assertArrayHasKey('location_aggregations', $this->query['aggregations']);
    }

    public function test_it_appends_the_query_to_the_filter_aggregations_filter()
    {
        $this->assertArrayHasKey('filter_aggregations', $this->query['aggregations']);
        $this->assertIsArray($this->query['aggregations']['filter_aggregations']);
        $this->assertArrayHasKey('filter', $this->query['aggregations']['filter_aggregations']);
        $this->assertIsArray($this->query['aggregations']['filter_aggregations']['filter']);
        $this->assertSame($this->expectedQuery, $this->query['aggregations']['filter_aggregations']['filter']);
    }

    public function test_it_appends_the_query_to_the_location_aggregations_filter()
    {
        $this->assertArrayHasKey('location_aggregations', $this->query['aggregations']);
        $this->assertIsArray($this->query['aggregations']['location_aggregations']);
        $this->assertArrayHasKey('filter', $this->query['aggregations']['location_aggregations']);
        $this->assertIsArray($this->query['aggregations']['location_aggregations']['filter']);
        $this->assertSame($this->expectedQuery, $this->query['aggregations']['location_aggregations']['filter']);
    }

    public function test_it_generates_the_correct_range_if_only_one_value_is_passed()
    {
        $sliderQuery = new SliderQueryBuilder('existingPrice', '1000');
        $query = $sliderQuery->query();
        $this->assertSame([
            'bool' => [
                'filter' => [
                    [
                        'range' => [
                            'existingPrice' => [
                                'gte' => '1000'
                            ]
                        ]
                    ]
                ]
            ]
        ], $query['post_filter']);
    }
}
