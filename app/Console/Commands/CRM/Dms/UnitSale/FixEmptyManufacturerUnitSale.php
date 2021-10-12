<?php

namespace App\Console\Commands\CRM\Dms\UnitSale;

use App\Models\CRM\Dms\UnitSale;
use Illuminate\Console\Command;

class FixEmptyManufacturerUnitSale extends Command
{
    protected $signature = 'crm:dms:unit-sale:fix-empty-make';

    protected $description = 'Fixes Quotes with Empty Makes';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $unitSales = UnitSale::where('inventory_manufacturer', '')
                                ->where('inventory_id', '!=', 0)
                                ->get();

        foreach($unitSales as $unitSale) {
            if (empty($unitSale->inventory)) {
                continue;
            }
            $this->info("Setting make to {$unitSale->inventory->manufacturer} for quote id {$unitSale->id}");
            $unitSale->inventory_manufacturer = $unitSale->inventory->manufacturer;
            $unitSale->save();
        }


    }
}
