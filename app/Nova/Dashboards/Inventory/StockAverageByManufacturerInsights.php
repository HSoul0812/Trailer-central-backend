<?php

declare(strict_types=1);

namespace App\Nova\Dashboards\Inventory;

use App\Nova\Http\Requests\Inventory\StockAverageRequest;
use App\Nova\Http\Requests\Inventory\StockAverageRequestInterface;
use App\Nova\Http\Requests\WithCardRequestBindings;
use App\Services\Inventory\StockAverageByManufacturerServiceInterface;
use App\Support\CriteriaBuilder;
use Dingo\Api\Routing\Helpers;
use Laravel\Nova\Dashboard;
use stdClass;
use TrailerTrader\Insights\AreaChart;

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
            $insights = $this->service->collect(new CriteriaBuilder([
                'period'       => $request->getPeriod(),
                'from'         => $request->getFrom(),
                'to'           => $request->getTo(),
                'manufacturer' => $request->getSubset(),
            ]));

            $series = [
                [
                    'barPercentage'   => 0.5,
                    'label'           => 'Industry Average',
                    'borderColor'     => '#1FE074',
                    'backgroundColor' => 'rgba(31, 224, 116, 0.2)',
                    'data'            => $insights->complement,
                ],
            ];

            if (!is_null($insights->subset)) {
                $series[] = [
                    'barPercentage'   => 0.5,
                    'label'           => $request->getSubset(),
                    'borderColor'     => '#008AC5',
                    'backgroundColor' => 'rgba(0, 138, 197, 0.2)',
                    'data'            => $insights->subset,
                ];
            }

            $manufacturerList = $this->service
                ->getAllManufacturers()
                ->map(fn (stdClass $item) => [
                    'text' => $item->manufacturer, 'value' => $item->manufacturer,
                ])
                ->prepend(['value' => '', 'text' => 'Manufacturer'])
                ->toArray();

            return [
                (new AreaChart())
                    ->title('YOY % CHANGE')
                    ->animations([
                        'enabled' => true,
                        'easing'  => 'easeinout',
                    ])
                    ->series($series)
                    ->filters([
                        'subset' => [
                            'show'     => true,
                            'list'     => $manufacturerList,
                            'default'  => 'Manufacturer',
                            'selected' => $request->getSubset(),
                        ],
                        'period' => [
                            'selected' => $request->getPeriod(),
                        ],
                    ])
                    ->options([
                        'xAxis' => [
                            'categories' => $insights->legends,
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
