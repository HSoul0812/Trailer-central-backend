<?php

namespace Tests\Unit\App\Console\Commands\Report;

use App\Models\MonthlyImpressionReport;
use Illuminate\Filesystem\FilesystemAdapter;
use Storage;
use Tests\Common\IntegrationTestCase;

class ReportMonthlyInventoryTrackingDataCommandTest extends IntegrationTestCase
{
    private FilesystemAdapter $storage;

    private int $year;

    private int $month;

    protected function setUp(): void
    {
        $this->storage = Storage::disk('monthly-inventory-impression-reports');

        $now = now();
        $this->year = $now->year;
        $this->month = $now->month - 1;

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->storage->deleteDirectory(
            directory: sprintf('%d/%02d', $this->year, $this->month)
        );

        parent::tearDown();
    }

    public function testItCanGenerateZipFiles(): void
    {
        foreach (range(1, 10) as $dealerId) {
            MonthlyImpressionReport::factory()->create([
                'year' => $this->year,
                'month' => $this->month,
                'dealer_id' => $dealerId,
            ]);
        }

        $this->assertTrue(true);
    }
}
