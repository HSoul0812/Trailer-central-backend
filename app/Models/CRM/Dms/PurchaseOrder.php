<?php


namespace App\Models\CRM\Dms;


use Illuminate\Database\Eloquent\Model;

use App\Models\CRM\Dms\PurchaseOrderReceipt;

/**
 * Class PurchaseOrder
 * @package App\Models\CRM\Dms
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
