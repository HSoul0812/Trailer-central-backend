<?php

declare(strict_types=1);

namespace Tests\Integration\App\Nova\Dashboards;

use App\Nova\Dashboards\Inventory\PriceAverageByManufacturerInsights;
use App\Nova\Http\Requests\InsightRequestInterface;
use App\Nova\Http\Requests\Inventory\PriceAverageRequest;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Support\Facades\Date;
use Tests\Common\IntegrationTestCase;
use TrailerTrader\Insights\AreaChart;

/**
 * Since this method is implemented in an abstract class, and to avoid any mocking, this test case will cover all
 * those concrete classes using that abstract class.
 *
 * @covers \App\Nova\Dashboards\AbstractAverageByManufacturerInsights::cards
 */
class AbstractAverageByManufacturerInsightsTest extends IntegrationTestCase
{
    /**
     * @covers       \App\Nova\Dashboards\Inventory\StockAverageByManufacturerInsights::cards
     * @covers       \App\Nova\Dashboards\Leads\LeadsAverageByManufacturerInsights::cards
     * @covers       \App\Nova\Dashboards\Inventory\PriceAverageByManufacturerInsights::cards
     *
     * @dataProvider invalidParametersProvider
     */
    public function testWithInvalidParameters(
        array $params,
        string $expectedException,
        string $expectedExceptionMessage,
        $expectedErrorMessages
    ): void {
        $dashboard = app(PriceAverageByManufacturerInsights::class);

        $paramsExtracted = $this->extractValues($params);

        $request = new PriceAverageRequest($paramsExtracted);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            $dashboard->cards($request);
        } catch (ResourceException $exception) {
            self::assertSame($expectedErrorMessages, $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @covers       \App\Nova\Dashboards\Inventory\StockAverageByManufacturerInsights::cards
     * @covers       \App\Nova\Dashboards\Leads\LeadsAverageByManufacturerInsights::cards
     * @covers       \App\Nova\Dashboards\Inventory\PriceAverageByManufacturerInsights::cards
     *
     * @dataProvider validParametersProvider
     */
    public function testWithValidParameters(array $params): void
    {
        $dashboard = app(PriceAverageByManufacturerInsights::class);

        $paramsExtracted = $this->extractValues($params);

        $request = new PriceAverageRequest($paramsExtracted);

        $response = $dashboard->cards($request);

        self::assertIsArray($response);
        self::assertInstanceOf(AreaChart::class, $response[0]);
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message.
     *
     * @return array<string, array>
     *
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function invalidParametersProvider(): array
    {
        return [          // array $params, string $expectedException, string $expectedExceptionMessage, string|array $firstExpectedErrorMessage
            'wrong period' => [['period' => 'yearly'], ResourceException::class, 'Validation Failed', 'The selected period is invalid.'],
            'wrong from' => [['from' => '33-33-3333'], ResourceException::class, 'Validation Failed', 'The from does not match the format Y-m-d.'],
            'wrong to' => [['from' => '2021-09-09', 'to' => '2021-09-07'], ResourceException::class, 'Validation Failed', 'The from must be a date before or equal to 2021-09-07.'],
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message.
     *
     * @return array<string, array>
     *
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function validParametersProvider(): array
    {
        $now = Date::now()->format('Y-m-d');

        return [          // array $params
            'no parameters' => [[]],
            'valid period' => [['period' => InsightRequestInterface::PERIOD_PER_DAY]],
            'valid from' => [['from' => $now]],
            'valid to' => [['from' => Date::now()->startOf('year')->format('Y-m-d'), 'to' => $now]],
        ];
    }
}
