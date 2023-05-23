<?php

declare(strict_types=1);

namespace Tests\Integration\App\Nova\Dashboards\Leads;

use App\Nova\Dashboards\Leads\LeadsAverageByManufacturerInsights;
use App\Nova\Http\Requests\InsightRequestInterface;
use App\Nova\Http\Requests\Leads\LeadsAverageRequest;
use Database\Seeders\Leads\AverageSeeder;
use Tests\Common\IntegrationTestCase;
use TrailerTrader\Insights\AreaChart;

class LeadsAverageByManufacturerInsightsTest extends IntegrationTestCase
{
    // protected string $seeder = AverageSeeder::class;

    protected LeadsAverageByManufacturerInsights $dashboard;

    public function setUp(): void
    {
        parent::setUp();

        $this->dashboard = app(LeadsAverageByManufacturerInsights::class);
    }

    public function testByWeeks(): void
    {
        $this->markTestSkipped(
            "This test is skipped because it's not relevant to Trailertrader"
        );

        $request = new LeadsAverageRequest();

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
        self::assertEquals(6, $series->first());
        self::assertEquals(3, $series->get(38));
        self::assertEquals(0, $series->last());

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

        $request = new LeadsAverageRequest(['period' => InsightRequestInterface::PERIOD_PER_DAY]);
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
        self::assertEquals(3, $series->first());
        self::assertEquals(3, $series->get(262));
        self::assertEquals(0, $series->last());

        // option categories assertions
        self::assertArrayHasKey('options', $meta);
        self::assertObjectHasAttribute('xAxis', $meta['options']);

        $categories = collect($meta['options']->xAxis['categories']);

        self::assertArrayHasKey('categories', $meta['options']->xAxis);
        self::assertGreaterThanOrEqual(278, $categories->count());
        self::assertSame('2021-01-01', $categories->first());
    }
}
