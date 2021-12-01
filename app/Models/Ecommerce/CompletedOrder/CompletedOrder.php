<?php

namespace App\Models\Ecommerce\CompletedOrder;

use App\Models\Traits\ErrorAware;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dealer_id
 * @property string $po_number
 * @property float $total_amount
 * @property float $tax
 * @property float $handling_fee
 * @property float $shipping_fee
 * @property string $payment_status 'paid', 'unpaid'
 * @property string $refund_status i.e 'unrefunded', 'refunded', 'partial_refunded'
 * @property array<int> $refunded_parts part's ids
 * @property float $total_refunded_amount
 * @property float $adjustment_refunded_amount a custom refunded amount
 * @property float $parts_refunded_amount
 * @property float $shipping_refunded_amount
 * @property float $handling_refunded_amount
 * @property float $tax_refunded_amount
 * @property string $payment_method
 * @property string $payment_intent the payment unique id
 * @property array<array<int, int, float>> $parts i.e: [{id:int, qty: int, price: float}]
 * @property int $ecommerce_customer_id
 * @property string $ecommerce_cart_id
 * @property int $ecommerce_order_id
 * @property string $ecommerce_order_code a long unique code
 * @property array $ecommerce_items
 * @property string $shipping_carrier_code
 * @property string $shipping_method_code
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property \DateTimeInterface $refunded_at
 *
 * @property-read User $dealer
 *
 * @method static Collection|static create(array $attributes = [])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class CompletedOrder extends Model
{
    use TableAware;
    use ErrorAware;

    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_UNPAID = 'unpaid';

    public const REFUND_STATUS_UNREFUNDED = 'unrefunded';
    public const REFUND_STATUS_REFUNDED = 'refunded';
    public const REFUND_STATUS_PARTIAL_REFUNDED = 'partial_refunded';

    public const ERROR_STAGE_TEXTRAIL_REMOTE_SYNC = 'textrail_remote_sync';
    public const ERROR_STAGE_TEXTRAIL_GET_ORDER = 'textrail_remote_get_order';

    public const ECOMMERCE_STATUS_NOT_APPROVED = 'not_approved';
    public const ECOMMERCE_STATUS_APPROVED = 'approved';
    public const ECOMMERCE_STATUS_CANCELED = 'canceled';


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

    const ECOMMERCE_ORDER_STATUSES = [
        'not_approved',
        'approved',
        'canceled'
    ];

    protected $fillable = [
        'dealer_id',
        'po_number',
        'customer_email',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_intent',
        'refund_status',
        'parts_refunded_amount',
        'shipping_refunded_amount',
        'handling_refunded_amount',
        'adjustment_refunded_amount',
        'tax_refunded_amount',
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
        'shipping_carrier_code',
        'shipping_method_code',
        'billing_address',
        'billing_name',
        'billing_country',
        'billing_city',
        'billing_region',
        'billing_zip',
        'parts',
        'tax',
        'tax_rate',
        'total_before_tax',
        'handling_fee',
        'shipping_fee',
        'subtotal',
        'in_store_pickup',
        'ecommerce_customer_id',
        'ecommerce_cart_id',
        'ecommerce_order_id',
        'ecommerce_order_code',
        'ecommerce_items',
        'ecommerce_order_status',
        'phone_number',
        'invoice_pdf_url',
        'invoice_id',
        'invoice_url'
    ];

    /** @var array */
    protected $guarded = [
        'created_at'
    ];

    /** @var array */
    protected $dates = [
        'created_at',
        'updated_at',
        'refunded_at',
        'failed_at',
    ];

    protected $casts = [
        'parts' => 'json',
        'refunded_parts' => 'json',
        'ecommerce_items' => 'json',
        'errors' => 'json'
    ];

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isRefundable(): bool
    {
        return in_array(
            $this->refund_status,
            [self::REFUND_STATUS_PARTIAL_REFUNDED, self::REFUND_STATUS_UNREFUNDED]
        );
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }
}
