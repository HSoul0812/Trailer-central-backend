<?php

declare(strict_types=1);

namespace App\Nova\Dashboards\Inventory;

use App\Nova\Http\Requests\Inventory\StockAverageRequest;
use App\Nova\Http\Requests\Inventory\StockAverageRequestInterface;
use App\Nova\Http\Requests\WithCardRequestBindings;
use App\Services\Inventory\StockAverageByManufacturerServiceInterface;
use App\Support\CriteriaBuilder;
use Coroowicaksono\ChartJsIntegration\AreaChart;
use Dingo\Api\Routing\Helpers;
use Laravel\Nova\Dashboard;

class StockAverageByManufacturerInsights extends Dashboard
{
    use WithCardRequestBindings;
    use Helpers;

    public function __construct(private StockAverageByManufacturerServiceInterface $service, ?string $component = null)
    {
        $this->constructRequestBindings();

        parent::__construct($component);
    }

    /**
     * Get the cards for the dashboard.
     */
    public function cards(StockAverageRequestInterface $request): array
    {
        if ($request->validate()) {
            $data = $this->service->getAll(new CriteriaBuilder([
                'period'       => $request->getPeriod(),
                'from'         => $request->getFrom(),
                'to'           => $request->getTo(),
                'manufacturer' => $request->getAggregateValue(),
            ]));

            //@todo prepare the data to be provided to the chart

            $labels = [
                'industryAverage'   => 'Industry Average',
                'manufacturerBrand' => 'Manufacturer / Brand',
                'category'          => 'category',
                'state'             => 'state',
                'year'              => 'year',
            ];

            $payload = [
                'data' => [
                    'industryAverage'   => [50, 55, 60, 53, 48, 45, 40],
                    'manufacturerBrand' => [55, 52, 55, 51, 50, 55, 45],
                ],
                'meta' => [
                    'labels' => [
                        'xAxis' => [
                            '01-05-2021',
                            '05-06-2021',
                            '11-07-2021',
                            '21-08-2021',
                            '09-09-2021',
                            '25-10-2021',
                            '06-11-2021',
                        ],
                        'yAxis' => [],
                    ],
                    'summaries' => [
                        'yoyChangePercent' => 2021,
                    ],
                ],
            ];

            return [
                (new AreaChart())
                    ->title('YOY % CHANGE')
                    ->animations([
                        'enabled' => true,
                        'easing'  => 'easeinout',
                    ])
                    ->series([
                        [
                            'barPercentage'   => 0.5,
                            'label'           => $labels['industryAverage'],
                            'borderColor'     => '#1FE074',
                            'backgroundColor' => 'rgba(31, 224, 116, 0.2)',
                            'data'            => $payload['data']['industryAverage'],
                        ], [
                            'barPercentage'   => 0.5,
                            'label'           => $labels['manufacturerBrand'],
                            'borderColor'     => '#008AC5',
                            'backgroundColor' => 'rgba(0, 138, 197, 0.2)',
                            'data'            => $payload['data']['manufacturerBrand'],
                        ],
                    ])
                    ->options([
                        'btnReload' => true,
                        'extLink'   => 'xx',
                        'xaxis'     => [
                            'categories' => $payload['meta']['labels']['xAxis'],
                        ],
                    ]),
            ];
        }

        $this->response->errorBadRequest();
    }

    /**
     * Get the URI key for the metric.
     */
    public static function uriKey(): string
    {
        return 'stock-average-by-manufacturer-insights';
    }

    public static function label(): string
    {
        return 'Stock AVG by manufacturer';
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(
            StockAverageRequestInterface::class,
            fn () => inject_request_data(StockAverageRequest::class)
        );

        app()->bindMethod(
            __CLASS__ . '@cards',
            fn (self $class) => $class->cards(app()->make(StockAverageRequestInterface::class))
        );
    }
}
