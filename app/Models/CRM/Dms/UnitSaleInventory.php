<?php


namespace App\Models\CRM\Dms;


use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UnitSaleInventory
 * @package App\Models\CRM\Dms
 * @property Inventory $inventory
 * @property UnitSale $unitSale
 */
class UnitSaleInventory extends Model
{
    protected $table = 'dms_quote_inventory';

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'inventory_id', 'inventory_id');
    }

    public function unitSale()
    {
        return $this->belongsTo(UnitSale::class, 'quote_id', 'id');
    }
}
