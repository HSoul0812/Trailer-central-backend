<?php

declare(strict_types=1);

use App\Models\CRM\Dms\Quickbooks\Item;
use App\Models\CRM\Dms\ServiceOrder;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

class PopulateQbInvoiceItemInventoryData extends Migration
{
    /** @var InventoryServiceInterface */
    private $service;

    public function __construct()
    {
        $this->service = App::make(InventoryServiceInterface::class);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // those inventories sold since January 1, 2021
        DB::table('qb_invoice_items AS ii')
            ->select(
                'ii.id',
                'iy.inventory_id',
                'iy.total_of_cost',
                'iy.cost_of_unit',
                'iy.true_cost',
                'iy.cost_of_shipping',
                'iy.cost_of_prep',
                'iy.pac_type',
                'iy.pac_amount'
            )
            ->selectRaw(sprintf("(
                    SELECT SUM(ro.total_price) as total_price
                    FROM dms_repair_order ro
                    WHERE ro.inventory_id = i.id AND ro.type = '%s'
                    GROUP BY ro.inventory_id
                    ) as cost_of_ros", ServiceOrder::TYPE_INTERNAL
                )
            )
            ->join('qb_invoices as iv', 'iv.id', '=', 'ii.invoice_id')
            ->join('qb_items as i', 'i.id', '=', 'ii.item_id', 'inner')
            ->join('inventory as iy', 'iy.inventory_id', '=', 'i.item_primary_id')
            ->where('i.type', '=', Item::ITEM_TYPES['TRAILER'])
            ->where('iv.invoice_date', '>=', '2021-01-01')
            ->orderBy('ii.id')
            ->chunk(100, function (Collection $invoiceItems): void {
                $invoiceItems->each(function (stdClass $item): void {
                    $this->saveItem($item);
                });
            });

        exit; // just for develop
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // nothing relevant to restore
    }

    private function saveItem(stdClass $item): void
    {
        $trueCost = (float)$item->true_cost;
        $costOfUnit = (float)$item->cost_of_unit;
        $costOfShipping = (float)$item->cost_of_shipping;
        $costOfPrep = (float)$item->cost_of_prep;
        $costOfRos = (float)$item->cost_of_ros;
        $pacAmount = (float)$item->pac_amount;

        // if the total cost hasn't been calculated yet through the UI, it will calculate here
        $totalOfCost = $item->total_of_cost > 0 ?
            (float)$item->total_of_cost :
            $this->service->calculateTotalOfCost($costOfUnit, $costOfShipping, $costOfPrep, $costOfRos)
                ->getAmount()
                ->toFloat();

        $costOverhead = $this->service->calculateCostOverhead($totalOfCost, $pacAmount, $item->pac_type)
            ->getAmount()
            ->toFloat();

        $trueTotalCost = $this->service->calculateTrueTotalCost($trueCost, $costOfShipping, $costOfPrep, $costOfRos)
            ->getAmount()
            ->toFloat();

        DB::table('qb_invoice_item_inventories')->updateOrInsert(
            ['invoice_item_id' => $item->id],
            [
                'invoice_item_id' => $item->id,
                'inventory_id' => $item->inventory_id,
                'cost_overhead' => $costOverhead,
                'true_total_cost' => $trueTotalCost
            ]
        );
    }
}
