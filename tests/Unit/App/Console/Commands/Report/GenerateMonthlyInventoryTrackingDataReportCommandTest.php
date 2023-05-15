<?php

namespace Tests\Unit\App\Console\Commands\Report;

use App\Console\Commands\Report\GenerateMonthlyInventoryTrackingDataReportCommand;
use App\Models\MonthlyImpressionReport;
use Illuminate\Support\Facades\Storage;
use Tests\Common\TestCase;

class GenerateMonthlyInventoryTrackingDataReportCommandTest extends TestCase
{
    public function testItCanValidateInput(): void
    {
        $this
            ->artisan(GenerateMonthlyInventoryTrackingDataReportCommand::class, [
                'year' => 'invalid',
                'month' => 'invalid',
            ])
            ->assertExitCode(1);
    }

    public function testItCanGenerateZipFiles(): void
    {
        $now = now();

        $year = $now->year;
        $month = $now->month - 1;

        $storage = Storage::disk('monthly-inventory-impression-reports');

        foreach (range(1, 3) as $dealerId) {
            MonthlyImpressionReport::factory()->create([
                'year' => $year,
                'month' => $month,
                'dealer_id' => $dealerId,
            ]);
        }

        $this->artisan(GenerateMonthlyInventoryTrackingDataReportCommand::class, [
            'year' => $year,
            'month' => $month,
        ]);

        $directory = sprintf('%d/%02d', $year, $month);

        $files = $storage->files($directory);

        $this->assertCount(3, $files);

        $mimeType = $storage->mimeType($files[0]);

        $this->assertEquals('application/gzip', $mimeType);

        $storage->deleteDirectory($directory);
    }
}
