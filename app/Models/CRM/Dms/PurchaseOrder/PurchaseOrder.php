<?php


namespace App\Models\CRM\Dms\PurchaseOrder;


use Illuminate\Database\Eloquent\Model;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderReceipt;

/**
 * Class PurchaseOrder
 * @package App\Models\CRM\Dms\PurchaseOrder
 */
class PurchaseOrder extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_purchase_order';

    public $timestamps = false;

    public function receipts()
    {
        return $this->hasMany(PurchaseOrderReceipt::class);
    }
}
