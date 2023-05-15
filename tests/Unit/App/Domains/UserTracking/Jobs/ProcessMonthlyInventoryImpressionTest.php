<?php

namespace Tests\Unit\App\Domains\UserTracking\Jobs;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\UserTracking\Jobs\ProcessMonthlyInventoryImpression;
use App\Domains\UserTracking\Types\UserTrackingEvent;
use App\Models\MonthlyImpressionReport;
use App\Models\UserTracking;
use Exception;
use Tests\Common\TestCase;

class ProcessMonthlyInventoryImpressionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testItDoesNotCreateAnyRecordIfMetaIsNullOrEmptyOrNoRequiredData(): void
    {
        $userTracking1 = UserTracking::factory()->create([
            'meta' => null,
        ]);
        $userTracking2 = UserTracking::factory()->create([
            'meta' => [],
        ]);
        $userTracking3 = UserTracking::factory()->create([
            'meta' => [[
                'no_required_fields' => true,
            ]],
        ]);

        $job1 = new ProcessMonthlyInventoryImpression($userTracking1);
        $job2 = new ProcessMonthlyInventoryImpression($userTracking2);
        $job3 = new ProcessMonthlyInventoryImpression($userTracking3);

        $table = (new MonthlyImpressionReport())->getTable();

        $job1->handle();
        $this->assertDatabaseCount($table, 0);

        $job2->handle();
        $this->assertDatabaseCount($table, 0);

        $job3->handle();
        $this->assertDatabaseCount($table, 0);
    }

    /**
     * @throws Exception
     */
    public function testItCanCreateRecordIfProvidedValidMetaData(): void
    {
        $fakePayload = [
            'event' => UserTrackingEvent::IMPRESSION,
            'page_name' => GetPageNameFromUrlAction::PAGE_NAMES['TT_PLP'],
            'meta' => [[
                'stock' => 'Stock 1',
                'title' => 'Title 1',
                'type_id' => 1,
                'category' => 'Category 1',
                'dealer_id' => 1,
                'type_label' => 'Type 1',
                'inventory_id' => 1,
                'category_label' => 'Category 1',
            ], [
                'stock' => 'Stock 2',
                'title' => 'Title 2',
                'type_id' => 2,
                'category' => 'Category 2',
                'dealer_id' => 2,
                'type_label' => 'Type 2',
                'inventory_id' => 2,
                'category_label' => 'Category 2',
            ], [
                'stock' => 'Stock 3',
                'title' => 'Title 3',
                'type_id' => 3,
                'category' => 'Category 3',
                'dealer_id' => 3,
                'type_label' => 'Type 3',
                'inventory_id' => 3,
                'category_label' => 'Category 3',
            ]],
        ];

        // First round, test that the first userTracking is being inserted correctly
        $userTracking1 = UserTracking::factory()->create($fakePayload);
        $job1 = new ProcessMonthlyInventoryImpression($userTracking1);
        $table = (new MonthlyImpressionReport())->getTable();

        $job1->handle();

        $this->assertDatabaseCount($table, 3);

        $records = MonthlyImpressionReport::all();

        $this->assertEquals(1, $records[0]->plp_total_count);
        $this->assertEquals(1, $records[1]->plp_total_count);
        $this->assertEquals(1, $records[2]->plp_total_count);

        $fakePayload['meta'][] = [
            'stock' => 'Stock 4',
            'title' => 'Title 4',
            'type_id' => 4,
            'category' => 'Category 4',
            'dealer_id' => 4,
            'type_label' => 'Type 4',
            'inventory_id' => 4,
            'category_label' => 'Category 4',
        ];

        // Second round, test that the increment works correctly
        $userTracking2 = UserTracking::factory()->create($fakePayload);
        $job2 = new ProcessMonthlyInventoryImpression($userTracking2);
        $table = (new MonthlyImpressionReport())->getTable();

        $job2->handle();

        $this->assertDatabaseCount($table, 4);

        $records = MonthlyImpressionReport::all();

        $this->assertEquals(2, $records[0]->plp_total_count);
        $this->assertEquals(2, $records[1]->plp_total_count);
        $this->assertEquals(2, $records[2]->plp_total_count);
        $this->assertEquals(1, $records[3]->plp_total_count);
    }
}
