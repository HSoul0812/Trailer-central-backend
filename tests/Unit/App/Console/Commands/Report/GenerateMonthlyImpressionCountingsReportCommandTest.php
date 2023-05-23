<?php

namespace Tests\Unit\App\Console\Commands\Report;

use App\Console\Commands\Report\GenerateMonthlyImpressionCountingsReportCommand;
use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Models\MonthlyImpressionCounting;
use App\Models\MonthlyImpressionReport;
use Storage;
use Tests\Common\TestCase;

class GenerateMonthlyImpressionCountingsReportCommandTest extends TestCase
{
    public function testItCanValidateInput(): void
    {
        $this
            ->artisan(GenerateMonthlyImpressionCountingsReportCommand::class, [
                'year' => 'invalid',
                'month' => 'invalid',
            ])
            ->assertExitCode(1);
    }

    public function testItCanGenerateMonthlyImpressionCountingsZipFile(): void
    {
        $now = now();
        $year = $now->year;
        $month = $now->month - 1;
        $storage = Storage::disk('monthly-inventory-impression-countings-reports');
        $directory = sprintf('%s/%d/%02d', GetPageNameFromUrlAction::SITE_TT_AF, $year, $month);

        $storage->deleteDirectory($directory);

        $dealerIds = [1, 2, 3];
        $dealerIdCount = count($dealerIds);

        $expectedReportData = [];
        $expectedFileNames = [];

        foreach ($dealerIds as $dealerId) {
            $reports = MonthlyImpressionReport::factory()->times(10)->create([
                'year' => $year,
                'month' => $month,
                'dealer_id' => $dealerId,
                'site' => GetPageNameFromUrlAction::SITE_TT_AF,
            ]);

            $expectedReportData[$dealerId] = [
                'year' => $year,
                'month' => $month,
                'dealer_id' => $dealerId,
                'impressions_count' => 0,
                'views_count' => 0,
            ];

            /** @var MonthlyImpressionReport $report */
            foreach ($reports as $report) {
                $expectedReportData[$dealerId]['impressions_count'] += $report->plp_total_count;
                $expectedReportData[$dealerId]['views_count'] += ($report->pdp_total_count + $report->tt_dealer_page_total_count);
            }

            $expectedFileNames[] = "$directory/dealer-id-$dealerId.csv.gz";
        }

        $this->artisan(GenerateMonthlyImpressionCountingsReportCommand::class, [
            'year' => $year,
            'month' => $month,
        ]);

        $this->assertDatabaseCount((new MonthlyImpressionCounting())->getTable(), $dealerIdCount);

        foreach ($expectedReportData as $expectedReport) {
            $report = MonthlyImpressionCounting::query()
                ->site(GetPageNameFromUrlAction::SITE_TT_AF)
                ->yearMonthDealerId(
                    year: $expectedReport['year'],
                    month: $expectedReport['month'],
                    dealerId: $expectedReport['dealer_id'],
                )
                ->first();

            $this->assertEquals($expectedReport['impressions_count'], $report->impressions_count);
            $this->assertEquals($expectedReport['views_count'], $report->views_count);

            $this->assertTrue($storage->exists($report->zip_file_path));
        }

        $files = $storage->files($directory);

        $this->assertCount($dealerIdCount, $files);

        foreach ($expectedFileNames as $expectedFileName) {
            $this->assertContains($expectedFileName, $files);
        }

        $mimeType = $storage->mimeType($files[0]);

        $this->assertEquals('application/gzip', $mimeType);

        $storage->deleteDirectory($directory);
    }
}
