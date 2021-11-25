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
 * @property string $status 'pending', 'authorized', 'completed', 'return_received', 'failed'
 * @property string $recoverable_failure_stage 'payment_gateway', 'textrail'
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

    public const STATUS_PENDING = 'pending';
    public const STATUS_AUTHORIZED = 'authorized'; // the refund has been authorized by TexTrail
    public const STATUS_RETURN_RECEIVED = 'return_received'; // the refund should be proceeded by payment gateway
    public const STATUS_COMPLETED = 'completed'; // the refund has successfully been processed by the payment gateway
    public const STATUS_FAILED = 'failed';


    // these are intended to advice that some refund has failed after their successfully done remote process
    // subsequently, the refund will be marked as failed, but TrailerCentral can still recover it
    public const RECOVERABLE_STAGE_PAYMENT_GATEWAY_REFUND = 'payment_gateway_recoverable_refund';
    public const RECOVERABLE_STAGE_TEXTRAIL_ISSUE = 'textrail_recoverable_issue';

    public const ERROR_STAGE_TEXTRAIL_ISSUE_REMOTE = 'textrail_issue_remote';
    public const ERROR_STAGE_TEXTRAIL_ISSUE_LOCAL = 'textrail_issue_local';
    public const ERROR_STAGE_PAYMENT_GATEWAY_REFUND_LOCAL = 'payment_gateway_refund_remote';

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
}
