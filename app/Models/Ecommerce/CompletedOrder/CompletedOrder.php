<?php

namespace App\Models\Ecommerce\CompletedOrder;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property float $total_amount
 * @property string $payment_status 'paid', 'unpaid'
 * @property string $refund_status i.e 'unrefunded', 'refunded', 'partial_refunded'
 * @property array<int> $refunded_parts part's ids
 * @property float $refunded_amount
 * @property string $payment_intent the payment unique id
 * @property array<array<int, int>> $parts i.e: [{id:int, qty: int}]
 */
class CompletedOrder extends Model
{
    use TableAware;

    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_UNPAID = 'unpaid';

    public const REFUND_STATUS_UNREFUNDED = 'unrefunded';
    public const REFUND_STATUS_REFUNDED = 'refunded';
    public const REFUND_STATUS_PARTIAL_REFUNDED = 'partial_refunded';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ecommerce_completed_orders';

    /**
     * @const array
     */
    const STATUS_FIELDS = [
        'dropshipped',
        'abandoned',
        'unfulfilled',
        'pending',
        'fulfilled',
        'manual'
    ];

    protected $fillable = [
        'customer_email',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_intent',
        'refund_status',
        'refunded_amount',
        'refunded_parts',
        'event_id',
        'object_id',
        'stripe_customer',
        'shipping_address',
        'shipping_name',
        'shipping_country',
        'shipping_city',
        'shipping_region',
        'shipping_zip',
        'billing_address',
        'billing_name',
        'billing_country',
        'billing_city',
        'billing_region',
        'billing_zip',
        'parts'
    ];

    protected $casts = [
        'parts' => 'json',
        'refunded_parts' => 'json'
    ];

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_UNPAID;
    }

    public function isRefundable(): bool
    {
        return in_array(
            $this->refund_status,
            [self::REFUND_STATUS_PARTIAL_REFUNDED, self::REFUND_STATUS_UNREFUNDED]
        );
    }
}
