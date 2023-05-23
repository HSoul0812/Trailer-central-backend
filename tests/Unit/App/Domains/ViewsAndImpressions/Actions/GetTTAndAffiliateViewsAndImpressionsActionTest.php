<?php

namespace Tests\Unit\App\Domains\ViewsAndImpressions\Actions;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\ViewsAndImpressions\Actions\GetTTAndAffiliateViewsAndImpressionsAction;
use App\Domains\ViewsAndImpressions\DTOs\GetTTAndAffiliateViewsAndImpressionCriteria;
use App\Models\AppToken;
use App\Models\Dealer\ViewedDealer;
use App\Models\MonthlyImpressionCounting;
use Str;
use Tests\Common\TestCase;

class GetTTAndAffiliateViewsAndImpressionsActionTest extends TestCase
{
    public function testItCanFetchDataWithDefaultCriteria(): void
    {
        $viewsAndImpressions = resolve(GetTTAndAffiliateViewsAndImpressionsAction::class)->execute();

        $this->assertIsArray($viewsAndImpressions);
    }

    public function testItCanFetchDataAndFillEmptyMonths(): void
    {
        $this->createViewedDealers();

        $monthlyImpressionCountings = [[
            'year' => 2023,
            'month' => 4,
            'dealer_id' => 1,
            'impressions_count' => 20,
            'views_count' => 30,
            'zip_file_path' => '2023/04/dealer-id-1.csv.gz',
        ], [
            'year' => 2023,
            'month' => 5,
            'dealer_id' => 1,
            'impressions_count' => 40,
            'views_count' => 50,
            'zip_file_path' => '2023/05/dealer-id-1.csv.gz',
        ], [
            'year' => 2023,
            'month' => 4,
            'dealer_id' => 2,
            'impressions_count' => 80,
            'views_count' => 90,
            'zip_file_path' => '2023/04/dealer-id-2.csv.gz',
        ]];

        foreach ($monthlyImpressionCountings as $monthlyImpressionCounting) {
            MonthlyImpressionCounting::create($monthlyImpressionCounting);
        }

        $criteria = new GetTTAndAffiliateViewsAndImpressionCriteria();

        $viewsAndImpressions = resolve(GetTTAndAffiliateViewsAndImpressionsAction::class)
            ->setCriteria($criteria)
            ->execute();

        // Convert any PHP object inside the 'data' key to associative array
        $viewsAndImpressions = json_decode(json_encode($viewsAndImpressions), true);

        $this->assertIsArray($viewsAndImpressions);

        $expectedResponse = [
            [
                'dealer_id' => 1,
                'name' => 'Dealer 1',
                'statistics' => [
                    [
                        'year' => 2023,
                        'month' => 5,
                        'impressions_count' => 40,
                        'views_count' => 50,
                        'zip_file_download_path' => $this->expectedZipFileDownloadPath('2023/05/dealer-id-1.csv.gz'),
                    ],
                    [
                        'year' => 2023,
                        'month' => 4,
                        'impressions_count' => 20,
                        'views_count' => 30,
                        'zip_file_download_path' => $this->expectedZipFileDownloadPath('2023/04/dealer-id-1.csv.gz'),
                    ],
                ],
            ],
            [
                'dealer_id' => 2,
                'name' => 'Dealer 2',
                'statistics' => [
                    [
                        'year' => 2023,
                        'month' => 5,
                        'impressions_count' => 0,
                        'views_count' => 0,
                        'zip_file_download_path' => null,
                    ],
                    [
                        'year' => 2023,
                        'month' => 4,
                        'impressions_count' => 80,
                        'views_count' => 90,
                        'zip_file_download_path' => $this->expectedZipFileDownloadPath('2023/04/dealer-id-2.csv.gz'),
                    ],
                ],
            ],
        ];

        $this->assertEqualsCanonicalizing($expectedResponse, $viewsAndImpressions['data']);

        $expectedMeta = [
            'time_ranges' => [[
                'year' => 2023,
                'month' => 5,
            ], [
                'year' => 2023,
                'month' => 4,
            ]],
        ];

        $this->assertEqualsCanonicalizing($expectedMeta, $viewsAndImpressions['meta']);
    }

    public function testItCanPerformCorrectQueryWhenGivenTheProperCriteria(): void
    {
        $this->createViewedDealers();

        $appToken = AppToken::factory()->create();

        $criteria = new GetTTAndAffiliateViewsAndImpressionCriteria();

        $criteria->search = 'Dealer';
        $criteria->sortBy = GetTTAndAffiliateViewsAndImpressionCriteria::SORT_BY_DEALER_NAME;
        $criteria->sortDirection = GetTTAndAffiliateViewsAndImpressionCriteria::SORT_DIRECTION_DESC;

        $monthlyImpressionCountings = [[
            'year' => 2023,
            'month' => 4,
            'dealer_id' => 1,
            'impressions_count' => 20,
            'views_count' => 30,
            'zip_file_path' => GetPageNameFromUrlAction::SITE_TT_AF . '/2023/04/dealer-id-1.csv.gz',
        ], [
            'year' => 2023,
            'month' => 4,
            'dealer_id' => 2,
            'impressions_count' => 40,
            'views_count' => 50,
            'zip_file_path' => GetPageNameFromUrlAction::SITE_TT_AF . '/2023/04/dealer-id-2.csv.gz',
        ], [
            'year' => 2023,
            'month' => 4,
            'dealer_id' => 3,
            'impressions_count' => 80,
            'views_count' => 90,
            'zip_file_path' => GetPageNameFromUrlAction::SITE_TT_AF . '/2023/04/dealer-id-3.csv.gz',
        ]];

        foreach ($monthlyImpressionCountings as $monthlyImpressionCounting) {
            MonthlyImpressionCounting::create($monthlyImpressionCounting);
        }

        $viewsAndImpressions = resolve(GetTTAndAffiliateViewsAndImpressionsAction::class)
            ->setCriteria($criteria)
            ->setAppToken($appToken)
            ->execute();

        // Convert any PHP object inside the 'data' key to associative array
        $viewsAndImpressions = json_decode(json_encode($viewsAndImpressions), true);

        $this->assertIsArray($viewsAndImpressions);

        $this->assertCount(3, $viewsAndImpressions['data']);

        // Confirm that sorting is working
        $this->assertEquals('Dealer 3', data_get($viewsAndImpressions, 'data.0.name'));
        $this->assertEquals('Dealer 2', data_get($viewsAndImpressions, 'data.1.name'));
        $this->assertEquals('Dealer 1', data_get($viewsAndImpressions, 'data.2.name'));

        $expectedZipFileDownloadPath = $this->expectedZipFileDownloadPath(GetPageNameFromUrlAction::SITE_TT_AF . "/2023/04/dealer-id-3.csv.gz&app-token=$appToken->token");

        $this->assertEquals($expectedZipFileDownloadPath, data_get($viewsAndImpressions, 'data.0.statistics.0.zip_file_download_path'));

        // Make sure we don't have the 4th dealer in the data array
        $this->assertNull(data_get($viewsAndImpressions, 'data.3.name'));
    }

    private function expectedZipFileDownloadPath(string $zipFilePath): string
    {
        $zipFileDownloadPath = Str::of(config('app.url'))->rtrim('/');

        return $zipFileDownloadPath->append("/api/views-and-impressions/tt-and-affiliate/download-zip?file_path=$zipFilePath");
    }

    private function createViewedDealers(): void
    {
        ViewedDealer::factory()->create([
            'name' => 'Dealer 1',
            'dealer_id' => 1,
        ]);

        ViewedDealer::factory()->create([
            'name' => 'Dealer 2',
            'dealer_id' => 2,
        ]);

        ViewedDealer::factory()->create([
            'name' => 'Dealer 3',
            'dealer_id' => 3,
        ]);

        ViewedDealer::factory()->create([
            'name' => 'Someone Else',
            'dealer_id' => 4,
        ]);
    }
}
