<?php

namespace App\Models\CRM\Dms\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\Quickbooks\Item;

/**
 * @package App\Models\CRM\Dms\PurchaseOrder
 * @property-read PurchaseOrder $purchaseOrder
 */
class PurchaseOrderPart extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_purchase_order_parts';

    public $timestamps = false;

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function qbItem()
    {
        return $this->hasOne(Item::class, 'item_primary_id', 'part_id')->where('type', '=', Item::ITEM_TYPES['PART']);
    }

}
