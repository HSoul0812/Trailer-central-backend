<?php

namespace Tests\Integration\Commands\TrailerTrader;

use App\Console\Commands\TrailerTrader\ProcessExpiredInventoriesCommand;
use App\Models\Inventory\Inventory;
use Exception;
use Tests\Feature\Integration\IntegrationTest;

class ProcessExpiredInventoriesCommandTest extends IntegrationTest
{
    /**
     * @group TrailerTrader
     *
     * @return void
     * @throws Exception
     */
    public function testItCanProcessExpiredInventories(): void
    {
        $inventory1 = factory(Inventory::class)->create([
            'show_on_website' => 1,
            'tt_payment_expiration_date' => now(),
        ]);

        $inventory2 = factory(Inventory::class)->create([
            'show_on_website' => 0,
            'tt_payment_expiration_date' => null,
        ]);

        $inventory3 = factory(Inventory::class)->create([
            'show_on_website' => 1,
            'tt_payment_expiration_date' => now()->addMonth(),
        ]);

        $this->artisan(ProcessExpiredInventoriesCommand::class);

        $inventory1->refresh();
        $inventory2->refresh();
        $inventory3->refresh();

        $this->assertEquals(0, $inventory1->show_on_website);
        $this->assertEquals(0, $inventory2->show_on_website);
        $this->assertEquals(1, $inventory3->show_on_website);

        $inventory1->delete();
        $inventory2->delete();
    }
}
