<?php

namespace App\Models\CRM\Dms\PurchaseOrder;

use App\Models\User\NewDealerUser;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    const CRM_RECEIVE_PO_URL = '/accounting/purchase-order';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    protected $appends = [
        'receive_purchase_order_crm_url',
    ];

    public $timestamps = false;

    public function receipts()
    {
        return $this->hasMany(PurchaseOrderReceipt::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function getReceivePurchaseOrderCrmUrlAttribute(): string
    {
        return $this->status === self::STATUS_COMPLETED
            ? ''
            : $this->user->getCrmLoginUrl(
                self::CRM_RECEIVE_PO_URL . '?receive_po_id=' . $this->id,
                true
            );
    }
}
