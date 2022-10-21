<?php

namespace Tests\Integration\Commands\CRM\Dms\BackupRecovery;

use App\Console\Commands\CRM\Dms\BackupRecovery\RestoreInventoryIdForQuotes;
use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Models\CRM\Dms\UnitSale;
use App\Models\Inventory\Inventory;
use Artisan;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class RestoreInventoryIdForQuotesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testItCanRestoreInventoryIdForQuote()
    {
        // composer test -- tests/Integration --filter=testItCanRestoreInventoryIdForQuote
        $vin = Str::random();
        $dealerId = $this->getTestDealerId();

        $inventory = factory(Inventory::class)->create([
            'vin' => $vin,
        ]);
        $unitSale = factory(UnitSale::class)->create([
            'dealer_id' => $dealerId,
            'inventory_id' => $inventory->getKey(),
        ]);
        $inventory->delete();
        $inventory2Id = null;

        RestoreInventoryIdForQuotes::withBeforeFetchInventoryFromCurrentDbCallback(function (array $vins) use ($inventory, &$inventory2Id) {
            // If the inventory vin is not in the vins array, do nothing
            if (!in_array($inventory->vin, $vins)) {
                return;
            }

            // Otherwise, we delete this inventory and create a new one with the same vin
            // this way, we allow the command to sync the inventory2 id with the unit sale
            $inventory2 = factory(Inventory::class)->create([
                'vin' => $inventory->vin,
            ]);
            $inventory2Id = $inventory2->getKey();
            dd($inventory2);
            $inventory->delete();
        });

        Artisan::call(RestoreInventoryIdForQuotes::class, [
            'dealer_id' => $dealerId,
            'backup_db' => config('database.connections.mysql.host'),
        ]);

        $unitSale = $unitSale->refresh();

        $this->assertEquals($inventory2Id, $unitSale->inventory_id);
    }
}
