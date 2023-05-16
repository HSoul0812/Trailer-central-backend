<?php

namespace Tests\Unit\App\Domains\ViewsAndImpressions\Actions;

use App\Domains\ViewsAndImpressions\Actions\GetTTAndAffiliateViewsAndImpressionsAction;
use App\Domains\ViewsAndImpressions\DTOs\GetTTAndAffiliateViewsAndImpressionCriteria;
use App\Models\MonthlyImpressionCounting;
use Tests\Common\TestCase;

class GetTTAndAffiliateViewsAndImpressionsActionTest extends TestCase
{
    public function testItCanFetchDataWithDefaultCriteria()
    {
        $viewsAndImpressions = resolve(GetTTAndAffiliateViewsAndImpressionsAction::class)->execute();

        $this->assertIsArray($viewsAndImpressions);
    }

    /**
     * @dataProvider provideExpectations
     */
    public function testItCanFetchDataWithSetCriteria(array $dbRecords, array $requestCriteria, array $expectedResponse)
    {
        foreach ($dbRecords as $dbRecord) {
            MonthlyImpressionCounting::create($dbRecord);
        }

        $criteria = new GetTTAndAffiliateViewsAndImpressionCriteria();

        if (!empty($requestCriteria)) {
            $criteria->search = $requestCriteria['search'];
            $criteria->sortBy = $requestCriteria['sort_by'];
            $criteria->sortDirection = $requestCriteria['sort_direction'];
            $criteria->page = $requestCriteria['page'];
            $criteria->perPage = $requestCriteria['per_page'];
        }

        $viewsAndImpressions = resolve(GetTTAndAffiliateViewsAndImpressionsAction::class)
            ->setCriteria($criteria)
            ->execute();

        $this->assertEqualsCanonicalizing($expectedResponse, $viewsAndImpressions);
    }

    public function provideExpectations(): array
    {
        return [
            'empty data' => [
                [],
                [],
                [],
            ],
            'simple data with no criteria' => [
                [[
                    'year' => 2023,
                    'month' => 4,
                    'dealer_id' => 1,
                    'impressions_count' => 20,
                    'views_count' => 30,
                ], [], []],
                [],
                [],
            ],
        ];
    }
}
