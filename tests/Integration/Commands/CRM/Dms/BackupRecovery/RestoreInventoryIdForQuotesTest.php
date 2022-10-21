<?php

namespace Tests\Integration\Commands\CRM\Dms\BackupRecovery;

use App\Console\Commands\CRM\Dms\BackupRecovery\RestoreInventoryIdForQuotes;
use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Models\CRM\Dms\UnitSale;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Str;
use Tests\TestCase;

class RestoreInventoryIdForQuotesTest extends TestCase
{
    /**
     * In this test, we make sure that the code can restore the inventory id
     * for the quote if the current inventory_id no longer exist in the database
     *
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testItCanRestoreInventoryIdForQuote()
    {
        $vin = Str::random();
        $dealerId = $this->getTestDealerId();

        // Given that we have an inventory mapped to the unit sale
        // correctly in the backup database
        $inventory = factory(Inventory::class)->create([
            'vin' => $vin,
        ]);
        $unitSale = factory(UnitSale::class)->create([
            'dealer_id' => $dealerId,
            'inventory_id' => $inventory->getKey(),
        ]);

        /** @var Inventory|null $inventory2 */
        $inventory2 = null;

        // Before the code start fetching inventory in the current DB
        // we will delete the mapped inventory and create a new one and
        // set it to have the same vin number with the first one
        // so the unit sale will be mapped with this new inventory_id
        RestoreInventoryIdForQuotes::withBeforeFetchInventoryFromCurrentDbCallback(function (array $vins) use ($inventory, &$inventory2) {
            // If the inventory vin is not in the vins array, do nothing
            if (!in_array($inventory->vin, $vins)) {
                return;
            }

            $inventory2 = factory(Inventory::class)->create([
                'vin' => $inventory->vin,
            ]);
            $inventory->delete();
        });

        $expectedOutput = sprintf(
            "Unit Sale ID %s has updated inventory_id from %d to %d.",
            $unitSale->id,
            $unitSale->inventory_id,
            $inventory->getKey() + 1
        );

        $this
            ->artisan(RestoreInventoryIdForQuotes::class, [
                'dealer_id' => $dealerId,
                'backup_db' => config('database.connections.mysql.host'),
            ])
            ->expectsOutput($expectedOutput)
            ->assertExitCode(0);

        $unitSale = $unitSale->refresh();

        $this->assertEquals($inventory2->getKey(), $unitSale->inventory_id);

        // Once done, we clear the data
        // We cannot use the DatabaseTransactions trait because
        // the code won't be able to see the new record for the 1st
        // inventory and won't be able to delete it
        $inventory2->delete();
        $unitSale->delete();
    }

    /**
     * In this test, we make sure that if the inventory still exist in the database
     * the code will not change it to something else
     *
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testItKeepUsingTheSameInventoryIdIfNothingChanges()
    {
        $vin = Str::random();
        $dealerId = $this->getTestDealerId();

        $inventory = factory(Inventory::class)->create([
            'vin' => $vin,
        ]);
        $unitSale = factory(UnitSale::class)->create([
            'dealer_id' => $dealerId,
            'inventory_id' => $inventory->getKey(),
        ]);

        $expectedOutput = "Unit Sale ID $unitSale->id already has the correct inventory_id as $inventory->inventory_id, skipping this one.";

        $this
            ->artisan(RestoreInventoryIdForQuotes::class, [
                'dealer_id' => $dealerId,
                'backup_db' => config('database.connections.mysql.host'),
            ])
            ->expectsOutput($expectedOutput)
            ->assertExitCode(0);

        $unitSale = $unitSale->refresh();

        $this->assertEquals($inventory->getKey(), $unitSale->inventory_id);

        $inventory->delete();
        $unitSale->delete();
    }

    /**
     * In this test, we simulate the situation where the current DB
     * no longer has the inventory in the database. In this case,
     * the code should just ignore that unit_sale and continue to the next
     * one, leaving that unit_sale to still have the old inventory_id.
     *
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testItSkipsLoopIfNoVinFoundInCurrentDb()
    {
        $vin = Str::random();
        $dealerId = $this->getTestDealerId();

        $inventory = factory(Inventory::class)->create([
            'vin' => $vin,
        ]);
        $unitSale = factory(UnitSale::class)->create([
            'dealer_id' => $dealerId,
            'inventory_id' => $inventory->getKey(),
        ]);

        // Backup the inventory_id to use later
        $inventoryId = $inventory->getKey();

        RestoreInventoryIdForQuotes::withBeforeFetchInventoryFromCurrentDbCallback(function (array $vins) use ($inventory, &$inventory2) {
            // If the inventory vin is not in the vins array, do nothing
            if (!in_array($inventory->vin, $vins)) {
                return;
            }

            $inventory->delete();
        });

        $expectedOutput = "Cannot find VIN $vin from the current DB, skipping the Unit Sale ID $unitSale->id.";

        $this
            ->artisan(RestoreInventoryIdForQuotes::class, [
                'dealer_id' => $dealerId,
                'backup_db' => config('database.connections.mysql.host'),
            ])
            ->expectsOutput($expectedOutput)
            ->assertExitCode(0);

        $unitSale = $unitSale->refresh();

        $this->assertEquals($inventoryId, $unitSale->inventory_id);

        $unitSale->delete();
    }
}
