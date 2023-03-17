<?php

namespace Tests\Unit\App\Domains\UserTracking\Exporters;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\UserTracking\Exporters\InventoryViewAndImpressionCsvExporter;
use App\Models\UserTracking;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Storage;
use Str;
use Tests\Common\TestCase;

class InventoryViewAndImpressionCsvExporterTest extends TestCase
{
    /**
     * @throws FileNotFoundException
     */
    public function testItCanExportInventoryViewAndImpressionToCsv()
    {
        $filename = Str::random() . '.csv';

        $testDate = Carbon::parse('2023-03-15 00:00:00');

        Carbon::setTestNow($testDate);

        $userTracking1 = UserTracking::factory()->create([
            'url' => 'https://abc.com/something',
            'page_name' => GetPageNameFromUrlAction::PAGE_NAMES['TT_PLP'],
            'meta' => [[
                'dealer_id' => 123,
                'inventory_id' => 456,
            ]],
        ]);
        $userTracking2 = UserTracking::factory()->create([
            'url' => 'https://xyz.com/something',
            'page_name' => GetPageNameFromUrlAction::PAGE_NAMES['TT_PDP'],
            'meta' => [[
                'dealer_id' => 789,
                'inventory_id' => 112233,
            ]],
        ]);

        $exporter = resolve(InventoryViewAndImpressionCsvExporter::class)
            ->setFilename($filename)
            ->setFrom($testDate)
            ->setTo($testDate->clone()->endOfDay());

        $filePath = $exporter->export();

        $this->assertNotNull($filePath);

        $storage = Storage::disk('inventory-view-and-impression-reports');

        $content = $storage->get($filename);

        $this->assertStringContainsString(
            '123,456,abc.com,"2023-03-15 00:00:00",PLP',
            $content,
        );

        $this->assertStringContainsString(
            '789,112233,xyz.com,"2023-03-15 00:00:00",PDP',
            $content,
        );

        $storage->delete($filename);
    }
}
