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
use Insights\Filters\Filters;
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
            $insights = $this->service->collect(new CriteriaBuilder([
                'period'       => $request->getPeriod(),
                'from'         => $request->getFrom(),
                'to'           => $request->getTo(),
                'manufacturer' => $request->getAggregateValue(),
            ]));

            return [
                // new Filters(), // we need to add a card with the filters
                (new AreaChart())
                    ->title('YOY % CHANGE')
                    ->animations([
                        'enabled' => true,
                        'easing'  => 'easeinout',
                    ])
                    ->series([
                        [
                            'barPercentage'   => 0.5,
                            'label'           => 'Industry Average',
                            'borderColor'     => '#1FE074',
                            'backgroundColor' => 'rgba(31, 224, 116, 0.2)',
                            'data'            => $insights->complement,
                        ], [
                            'barPercentage'   => 0.5,
                            'label'           => 'Kz',
                            'borderColor'     => '#008AC5',
                            'backgroundColor' => 'rgba(0, 138, 197, 0.2)',
                            'data'            => $insights->subset,
                        ],
                    ])
                    ->options([
                        'xaxis' => [
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
