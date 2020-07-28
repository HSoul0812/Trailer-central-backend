<?php


namespace App\Models\CRM\Dms\PurchaseOrder;


use Illuminate\Database\Eloquent\Model;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrder;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPartReceived;

/**
 * Class PurchaseOrderReceipt
 * @package App\Models\CRM\Dms\PurchaseOrder
 */
class PurchaseOrderReceipt extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_purchase_order_receipt';

    const UPDATED_AT = null;

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receivedParts()
    {
        return $this->hasMany(PurchaseOrderPartReceived::class, 'po_receipt_id');
    }

}
