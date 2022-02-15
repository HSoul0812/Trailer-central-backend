<?php

namespace App\Models\CRM\Dms\UnitSale;

use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TradeIn extends Model
{
    use TableAware;

    const TABLE_NAME = 'dms_unit_sale_trade_in_v1';

    const ADD_INVENTORY_IMMEDIATELY = 1;
    const DO_NOT_ADD_INVENTORY_IMMEDIATELY = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
