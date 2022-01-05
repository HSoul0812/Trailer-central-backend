<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\CRM\Dms\UnitSale\TradeIn;
use App\Models\Inventory\Inventory;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class UpdateNullTradeInsStatusToAvailable extends Command
{
    const CHUNK_SIZE = 1000;

    protected $signature = 'crm:dms:update-null-trade-ins-status-to-available';

    protected $description = 'This command will update any status = NULL trade in inventories to 1 (available)';

    public function handle()
    {
        TradeIn::query()
            // Eager load the inventory to save query time
            ->with(['inventory' => function (HasOne $query) {
                // Fetch only the column that we need
                $query->select(['inventory_id', 'status', 'is_archived']);
            }])

            // Select only the columns that we need for this particular case
            ->select(['id', 'inventory_id'])

            // Only select the trade in that has associated inventory
            ->whereNotNull('inventory_id')

            // Start chunking the data into sets
            // then, process each set in a transaction to optimize DB usage
            ->chunkById(self::CHUNK_SIZE, function (Collection $tradeIns) {
                DB::transaction(function () use ($tradeIns) {
                    /** @var TradeIn $tradeIn */
                    foreach ($tradeIns as $tradeIn) {
                        $inventory = $tradeIn->inventory;

                        // No need to process the one that doesn't have inventory
                        if (is_null($inventory)) {
                            continue;
                        }

                        // No need to process the one that already has status
                        if (! is_null($inventory->status)) {
                            continue;
                        }

                        // Otherwise, update the status to
                        // 6 if it's an archived trade-ins
                        // 1 if it's not an archived trade-ins
                        $inventory->status = $inventory->is_archived ? Inventory::STATUS_QUOTE : Inventory::STATUS_AVAILABLE;
                        $inventory->save();

                        $this->info("Updated inventory id $inventory->inventory_id to status = $inventory->status.");
                    }
                });
            });

        $this->info("The command has finished.");
    }
}
