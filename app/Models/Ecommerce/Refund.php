<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
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
 * @property float $amount
 * @property array{id:int, amount: float} $parts a valid json array
 * @property string $reason
 * @property string $object_id
 * @property string $status 'finished', 'failed', 'recoverable_failure'
 * @property \DateTimeInterface $created_at
 * @property array $metadata a valid and useful json object (response, error, etc..)
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

    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    // this means that some done refund could be re-charge to be able rollback the refund process
    public const STATUS_RECOVERABLE_FAILURE = 'recoverable_failure';

    public const REASONS = [
        'duplicate',
        'fraudulent',
        'requested_by_customer'
    ];

    /** @var string */
    protected $table = 'ecommerce_order_refunds';

    /** @var bool */
    public $timestamps = false;

    protected $fillable = [
        'dealer_id',
        'order_id',
        'amount',
        'parts',
        'reason',
        'object_id',
        'metadata',
        'status'
    ];

    /** @var array */
    protected $guarded = [
        'created_at'
    ];

    /** @var array */
    protected $dates = [
        'created_at'
    ];

    protected $casts = [
        'parts' => 'array',
        'metadata' => 'json'
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
