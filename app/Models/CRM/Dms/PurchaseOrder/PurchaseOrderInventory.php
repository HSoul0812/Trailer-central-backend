<?php

namespace App\Models\CRM\Dms\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrder;
use App\Models\CRM\Dms\Quickbooks\Item;

/**
 * @package App\Models\CRM\Dms\PurchaseOrder
 */
class PurchaseOrderInventory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_purchase_order_inventory';

    public $timestamps = false;

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function qbItem()
    {
        return $this->belongsTo(Item::class, 'inventory_id', 'item_primary_id')->where('type', '=', Item::ITEM_TYPES['TRAILER']);
    }

}
