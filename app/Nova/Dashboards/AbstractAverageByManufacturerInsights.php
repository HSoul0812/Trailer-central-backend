<?php

declare(strict_types=1);

namespace App\Nova\Dashboards;

use App\Nova\Http\Requests\InsightRequestInterface;
use App\Nova\Http\Requests\WithCardRequestBindings;
use App\Services\AverageByManufacturerServiceInterface;
use App\Support\CriteriaBuilder;
use Dingo\Api\Routing\Helpers;
use Laravel\Nova\Dashboard;
use stdClass;
use TrailerTrader\Insights\AreaChart;

abstract class AbstractAverageByManufacturerInsights extends Dashboard
{
    use WithCardRequestBindings;
    use WithColorPalette;
    use Helpers;

    public function __construct(private AverageByManufacturerServiceInterface $service, ?string $component = null)
    {
        $this->constructRequestBindings();

        parent::__construct($component);
    }

    /**
     * Get the cards for the dashboard.
     *
     * @throws \Dingo\Api\Exception\ResourceException                when some validation error has appeared
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when some unknown error has appeared
     */
    public function cards(InsightRequestInterface $request): array
    {
        $data = $this->data($request);

        return [
            (new AreaChart())
                ->title('YOY % CHANGE')
                ->uriKey(static::uriKey())
                ->animations([
                    'enabled' => true,
                    'easing' => 'easeinout',
                ])
                ->series($data['series'])
                ->filters($data['filters'])
                ->options([
                    'xAxis' => [
                        'categories' => $data['legends'],
                    ],
                ]),
        ];
    }

    /**
     * @throws \Dingo\Api\Exception\ResourceException                when some validation error has appeared
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when some unknown error has appeared
     *
     * @return array{series: array<array>, legends: array<string>, filters: array<array>}
     */
    public function data(InsightRequestInterface $request): array
    {
        if ($request->validate()) {
            $criteriaBuilder = new CriteriaBuilder([
                'period' => $request->getPeriod(),
                'from' => $request->getFrom(),
                'to' => $request->getTo(),
                'manufacturer' => $request->getSubset(),
                'category' => $request->getCategory(),
            ]);

            $insights = $this->service->collect($criteriaBuilder);

            $series = [
                [
                    'barPercentage' => 0.5,
                    'label' => 'Industry Average',
                    'borderColor' => '#1FE074',
                    'backgroundColor' => 'rgba(31, 224, 116, 0.2)',
                    'data' => $insights->complement,
                ],
            ];

            if (!is_null($insights->subset)) {
                $colors = $this->generateColorPalette();
                $colorIndex = 0;

                foreach ($insights->subset as $title => $subset) {
                    if (count($colors) === $colorIndex) {
                        $colorIndex = 0;
                    }

                    $series[] = [
                        'barPercentage' => 0.5,
                        'label' => $title,
                        'borderColor' => $colors[$colorIndex],
                        'backgroundColor' => $this->hex2rgb($colors[$colorIndex]),
                        'data' => $subset,
                    ];

                    ++$colorIndex;
                }
            }

            $manufacturerList = $this->service
                ->getAllManufacturers($criteriaBuilder)
                ->map(fn (stdClass $item) => [
                    'text' => $item->manufacturer, 'value' => $item->manufacturer,
                ])
                ->toArray();

            $categoryList = $this->service
                ->getAllCategories($criteriaBuilder)
                ->map(fn (stdClass $item) => [
                    'text' => ucfirst(strtolower(str_replace(['_', '-'], [' ', ' '], $item->category))), 'value' => $item->category,
                ])
                ->toArray();

            return [
                'series' => $series,
                'legends' => $insights->legends,
                'filters' => [
                    'subset' => [
                        'show' => true,
                        'list' => $manufacturerList,
                        'selected' => $request->getSubset(),
                        'placeholder' => 'Select a manufacturer',
                    ],
                    'category' => [
                        'show' => true,
                        'list' => $categoryList,
                        'selected' => $request->getCategory(),
                    ],
                    'period' => [
                        'selected' => $request->getPeriod(),
                    ],
                    'datePicker' => [
                        'show' => true,
                        'dateRange' => [
                            'startDate' => $request->getFrom(),
                            'endDate' => $request->getTo(),
                        ],
                    ],
                ],
            ];
        }

        $this->response->errorBadRequest();
    }
}
