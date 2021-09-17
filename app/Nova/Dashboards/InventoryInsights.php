<?php

namespace App\Nova\Dashboards;

use Coroowicaksono\ChartJsIntegration\AreaChart;
use Laravel\Nova\Dashboard;

class InventoryInsights extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     */
    public function cards(): array
    {
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
                ->series([[
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
                ]])
                ->options([
                    'btnReload' => true,
                    'extLink'   => 'about:blank',
                    'xaxis'     => [
                        'categories' => $payload['meta']['labels']['xAxis'],
                    ],
                ]),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     */
    public static function uriKey(): string
    {
        return 'inventory-insights';
    }

    /**
     * Get the displayable name of the dashboard.
     */
    public static function label(): string
    {
        return 'Inventory Insights';
    }
}
