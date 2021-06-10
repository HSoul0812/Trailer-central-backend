<?php

namespace App\Models\CRM\Dms;

use App\Models\Pos\Sale;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

/**
 * Class Refund
 *
 * refunds
 *
 * @package App\Models\CRM\Dms
 * @property int $id
 * @property int $dealer_id
 * @property string $tb_name
 * @property int $tb_primary_id
 * @property float $amount
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property string $meta
 * @property int $qb_id
 * @property int $register_id
 *
 * @property RefundItem[] $items
 * @property Sale $sale the sale associated with this refund
 */
class Refund extends Model implements Filterable
{
    protected $table = "dealer_refunds";

    protected $casts = [
        'meta' => 'array', // field contains json data
    ];

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(RefundItem::class, 'dealer_refunds_id');
    }

    /**
     * @return BelongsTo
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'tb_primary_id', 'id');
    }

    /**
     * @param $name
     * @return array|\ArrayAccess|mixed
     */
    public function getMeta($name) {
        return Arr::get($this->meta, $name);
    }

    /**
     * @param $name
     * @param $value
     * @return array
     */
    public function setMeta($name, $value): array
    {
        return Arr::set($this->meta, $name, $value);
    }

    /**
     * @return string[]|null
     */
    public function jsonApiFilterableColumns(): ?array
    {
        return ['dealer_id'];
    }
}
