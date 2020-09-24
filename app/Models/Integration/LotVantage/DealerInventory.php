<?php

namespace App\Models\Integration\LotVantage;

use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class DealerInventory
 * @package App\Models\Integration\LotVantage
 */
class DealerInventory extends Model
{
    /**
     * @var string
     */
    protected $table = 'lotvantage_dealer_inventory';

    /**
     * @var string
     */
    protected $primaryKey = 'inventory_id';

    /**
     * @return HasOne
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
