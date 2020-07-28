<?php


namespace App\Models\CRM\Dms;


use Illuminate\Database\Eloquent\Model;

use App\Models\CRM\Dms\PurchaseOrder;

/**
 * Class PurchaseOrderReceipt
 * @package App\Models\CRM\Dms
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
}
