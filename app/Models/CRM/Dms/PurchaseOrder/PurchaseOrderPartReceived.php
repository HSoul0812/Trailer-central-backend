<?php

namespace App\Models\CRM\Dms\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPart;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderReceipt;

/**
 * @package App\Models\CRM\Dms\PurchaseOrder
 */
class PurchaseOrderPartReceived extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_purchase_order_parts_received';

    const UPDATED_AT = null;

    public function receipt()
    {
        return $this->belongsTo(PurchaseOrderReceipt::class, 'po_receipt_id');
    }

    public function purchaseOrderItem()
    {
        return $this->hasOne(PurchaseOrderPart::class, 'id', 'po_part_id');
    }

}
