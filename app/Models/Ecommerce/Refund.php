<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Models\Traits\ErrorAware;
use App\Models\Traits\TableAware;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property int $dealer_id
 * @property int $order_id
 * @property float $total_amount parts_amount + handling_amount + shipping_amount + adjustment_amount + tax_amount
 * @property float $parts_amount
 * @property float $handling_amount
 * @property float $shipping_amount
 * @property float $adjustment_amount a custom amount to refund, useful when the order is refunded for a reason other than the parts, handling or shipping
 * @property float $tax_amount
 * @property array<array{sku:string, title:string, id:int, amount: float, qty: int, price: float}> $parts a valid json array of parts (items to refund)
 * @property string $reason
 * @property string $payment_gateway_id the refund id on the payment gateway
 * @property int $textrail_rma the return id on textrail
 * @property int $textrail_refund_id the refund id on textrail
 * @property string $status 'pending', 'approved', 'denied', 'processing', 'processed', 'completed', 'failed'
 * @property string $recoverable_failure_stage 'payment_gateway_refund', 'textrail_issue_return', 'textrail_update_return_status', 'textrail_order_cancellation'
 * @property array $metadata a valid and useful json object
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 *
 * @property-read User $dealer
 * @property-read CompletedOrder $order
 *
 * @method static Builder select($columns = ['*'])
 * @method static self find(int $id)
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Collection|static create(array $attributes = [])
 * @method static static findOrFail($id, $columns = ['*'])
 */
class Refund extends Model
{
    use TableAware;
    use ErrorAware;

    /**
     * Status flow:
     *  1) when the refund is created from TC side (known as return), it might follow one of the below flows:
     *      a) pending -> denied
     *      b) pending -> approved -> processing -> processed -> completed
     *      c) pending -> failed (when something goes wrong before it was created on the Magento side)
     *  2) when the refund is created from TexTrail side (known as order cancellation), it will follow the below flow:
     *      pending -> processing -> processed
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_DENIED = 'denied';
    public const STATUS_FAILED = 'failed'; // something goes wrong before it was created on the Magento side
    public const STATUS_APPROVED = 'approved'; // the refund should be proceeded by payment gateway
    public const STATUS_PROCESSING = 'processing'; // the refund is being processed by the payment gateway
    public const STATUS_PROCESSED = 'processed'; // the refund was processed by the payment gateway
    public const STATUS_COMPLETED = 'completed'; // the refund has been created on Magento side once it has been processed by the payment gateway

    // these are intended to advice that some refund has failed after a successfully done remote process, subsequently,
    // the refund will be marked as recoverable_failure and TrailerCentral can still recover it
    public const RECOVERABLE_STAGE_PAYMENT_GATEWAY_REFUND = 'payment_gateway_refund';
    public const RECOVERABLE_STAGE_TEXTRAIL_ISSUE_RETURN = 'textrail_issue_return';
    public const RECOVERABLE_STAGE_TEXTRAIL_UPDATE_RETURN_STATUS = 'textrail_update_return_status';
    public const RECOVERABLE_STAGE_TEXTRAIL_ORDER_CANCELLATION = 'textrail_order_cancellation';
    public const RECOVERABLE_STAGE_TEXTRAIL_CREATE_REFUND = 'textrail_create_refund';

    public const ERROR_STAGE_TEXTRAIL_ISSUE_RETURN_REMOTE = 'textrail_issue_return_remote';
    public const ERROR_STAGE_TEXTRAIL_ISSUE_RETURN_LOCAL = 'textrail_issue_return_local';

    public const REASONS = [
        'duplicate',
        'fraudulent',
        'requested_by_customer',
        'requested_by_textrail',
    ];

    public const REASON_REQUESTED_BY_TEXTRAIL = 'requested_by_textrail';

    /** @var string */
    protected $table = 'ecommerce_order_refunds';

    protected $fillable = [
        'dealer_id',
        'order_id',
        'total_amount',
        'parts_amount',
        'handling_amount',
        'shipping_amount',
        'adjustment_amount',
        'tax_amount',
        'parts',
        'reason',
        'payment_gateway_id',
        'textrail_rma',
        'textrail_refund_id',
        'metadata',
        'status',
        'recoverable_failure_stage',
        'failed_at'
    ];

    /** @var array */
    protected $guarded = [
        'created_at'
    ];

    /** @var array */
    protected $dates = [
        'created_at',
        'updated_at',
        'failed_at',
    ];

    protected $casts = [
        'parts' => 'array',
        'metadata' => 'json',
        'errors' => 'json',
    ];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CompletedOrder::class, 'order_id', 'id');
    }

    public function canBeApproved(): bool
    {
        return self::STATUS_PENDING === $this->status;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }
}
