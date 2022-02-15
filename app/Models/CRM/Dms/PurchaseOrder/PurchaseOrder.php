<?php

namespace App\Models\CRM\Dms\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseOrder
 * @package App\Models\CRM\Dms\PurchaseOrder
 * @property string $status
 * @property string $user_defined_id
 */
class PurchaseOrder extends Model
{
    public const STATUS_COMPLETED = 'completed';

    public const TABLE_NAME = 'dms_purchase_order';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    public function receipts()
    {
        return $this->hasMany(PurchaseOrderReceipt::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
