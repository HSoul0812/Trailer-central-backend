<?php

/* @noinspection PhpNoReturnAttributeCanBeAddedInspection */
/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Integration\App\Nova\Dashboards\Inventory;

use App\Nova\Dashboards\Inventory\PriceAverageByManufacturerInsights;
use App\Nova\Http\Requests\InsightRequestInterface;
use App\Nova\Http\Requests\Inventory\PriceAverageRequest;
use Database\Seeders\Inventory\AveragePriceSeeder;
use Tests\Common\IntegrationTestCase;
use TrailerTrader\Insights\AreaChart;

/**
 * @covers \App\Nova\Dashboards\Inventory\PriceAverageByManufacturerInsights::cards
 */
class PriceAverageByManufacturerInsightsTest extends IntegrationTestCase
{
    protected string $seeder = AveragePriceSeeder::class;

    protected PriceAverageByManufacturerInsights $dashboard;

    public function setUp(): void
    {
        parent::setUp();

        $this->dashboard = app(PriceAverageByManufacturerInsights::class);
    }

    public function testByWeeks(): void
    {
        $this->markTestSkipped(
            "This test is skipped because it's not relevant to Trailertrader"
        );

        $request = new PriceAverageRequest();

        $response = $this->dashboard->cards($request);

        /** @var AreaChart $chart */
        $chart = $response[0];
        $meta = $chart->meta();

        self::assertIsArray($response);
        self::assertInstanceOf(AreaChart::class, $chart);

        // series assertions
        self::assertArrayHasKey('series', $meta);
        self::assertArrayHasKey('data', $meta['series'][0]);

        $series = collect($meta['series'][0]['data']);

        self::assertGreaterThanOrEqual(39, $series->count());
        self::assertEquals(779.1666666666666667, $series->first());
        self::assertEquals(729.1666666666666667, $series->last());

        // option categories assertions
        self::assertArrayHasKey('options', $meta);
        self::assertObjectHasAttribute('xAxis', $meta['options']);

        $categories = collect($meta['options']->xAxis['categories']);

        self::assertArrayHasKey('categories', $meta['options']->xAxis);
        self::assertGreaterThanOrEqual(39, $categories->count());
        self::assertSame('2020-53', $categories->first());
    }

    public function testByDays(): void
    {
        $this->markTestSkipped(
            "This test is skipped because it's not relevant to Trailertrader"
        );

        $request = new PriceAverageRequest(['period' => InsightRequestInterface::PERIOD_PER_DAY]);
        $response = $this->dashboard->cards($request);

        /** @var AreaChart $chart */
        $chart = $response[0];
        $meta = $chart->meta();

        self::assertIsArray($response);
        self::assertInstanceOf(AreaChart::class, $chart);

        // series assertions
        self::assertArrayHasKey('series', $meta);
        self::assertArrayHasKey('data', $meta['series'][0]);

        $series = collect($meta['series'][0]['data']);

        self::assertGreaterThanOrEqual(278, $series->count());
        self::assertEquals(779.1666666666666667, $series->first());
        self::assertEquals(729.1666666666666667, $series->last());

        // option categories assertions
        self::assertArrayHasKey('options', $meta);
        self::assertObjectHasAttribute('xAxis', $meta['options']);

        $categories = collect($meta['options']->xAxis['categories']);

        self::assertArrayHasKey('categories', $meta['options']->xAxis);
        self::assertGreaterThanOrEqual(278, $categories->count());
        self::assertSame('2021-01-01', $categories->first());
    }
}
