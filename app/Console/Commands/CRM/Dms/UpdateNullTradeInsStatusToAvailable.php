<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\CRM\Dms\UnitSale\TradeIn;
use App\Models\Inventory\Inventory;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UpdateNullTradeInsStatusToAvailable extends Command
{
    /** @var int The chunk by id size */
    const CHUNK_SIZE = 1000;

    protected $signature = 'crm:dms:update-null-trade-ins-status-to-available';

    protected $description = 'This command will update any status = NULL trade in inventories to 1 (available) or 6 (archived) depends on the archive flag';

    public function handle()
    {
        TradeIn::query()

            // Eager load the inventory to save query time
            ->with('inventory:inventory_id,status,is_archived')

            // Only fetch the trade in that has inventory
            ->whereHas('inventory', function (Builder $query) {
                $query
                    ->select(['inventory_id', 'status'])
                    ->whereNull('status');
            })

            // Select only the columns that we need for this particular case
            ->select(['id', 'inventory_id'])

            // Start chunking the data into sets
            // then, process each set in a transaction to optimize DB usage
            ->chunkById(self::CHUNK_SIZE, function (Collection $tradeIns) {
                DB::transaction(function () use ($tradeIns) {
                    /** @var TradeIn $tradeIn */
                    foreach ($tradeIns as $tradeIn) {
                        /** @var Inventory $inventory */
                        $inventory = $tradeIn->inventory;

                        // Update the status to:
                        // 6 if it's an archived trade-ins
                        // 1 if it's not an archived trade-ins
                        $inventory->status = $inventory->is_archived ? Inventory::STATUS_QUOTE : Inventory::STATUS_AVAILABLE;

                        // We don't want to update the updated_at column
                        // doing so would affect the sort filter, etc.
                        $inventory->timestamps = false;

                        $inventory->save();

                        $this->info("Updated inventory id $inventory->inventory_id to status = $inventory->status.");
                    }
                });
            });

        $this->info("The command has finished.");
    }
}
