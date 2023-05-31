<?php

namespace App\Console\Commands\TrailerTrader;

use App\Console\Traits\PrependsOutput;
use App\Console\Traits\PrependsTimestamp;
use App\Models\Inventory\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ProcessExpiredInventoriesCommand extends Command
{
    use PrependsOutput, PrependsTimestamp;

    private const CHUNK_SIZE = 100;

    protected $signature = 'tt:process-expired-inventories';

    protected $description = 'Process all the expired inventories';

    /**
     * It's very important that we update the show_on_website one by one
     * because if we use DB::update directly, the Laravel Scout job won't
     * be fired
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info("Command $this->name is running...");

        $startOfTomorrow = now()->addDay()->startOfDay();

        Inventory::query()
            ->whereNotNull('tt_payment_expiration_date')
            ->where('tt_payment_expiration_date', '<=', $startOfTomorrow)
            ->where('show_on_website', 1)
            ->chunkById(self::CHUNK_SIZE, function (Collection $inventories) {
                /** @var Inventory $inventory */
                foreach ($inventories as $inventory) {
                    $inventory->update([
                        'show_on_website' => 0,
                    ]);

                    $this->info("Inventory ID $inventory->inventory_id: show_on_website is now updated to 0!");
                }
            });

        $this->info("Command $this->name is finished!");

        return 0;
    }
}
