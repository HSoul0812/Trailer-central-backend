<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $dealer_location_id
 * @property string $fee_type
 * @property float $amount
 * @property string $title
 * @property int $is_state_taxed
 * @property int $is_county_taxed
 * @property int $is_local_taxed
 * @property float $is_additional
 * @property string $visibility possible enum values:  hidden, visible, visible_locked
 * @property string $accounting_class Adt Default Fees, Taxes & Fees Group 1, Taxes & Fees Group 2, Taxes & Fees Group 3
 *
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder select($select = null)
 * @method static \Illuminate\Database\Query\Builder areVisible()
 */
class DealerLocationQuoteFee extends Model
{
    use TableAware;

    /**
     * @var string
     */
    protected $table = 'dealer_location_quote_fee';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_location_id",
        "fee_type",
        "title",
        "amount",
        "is_additional",
        "is_state_taxed",
        "is_county_taxed",
        "is_local_taxed",
        "visibility",
        "accounting_class"
    ];

    protected $appends = [
        'name'
    ];

    /**
     * Query scope for limiting results to visible fees
     *
     * This method should not be called directly, and should instead be invoked via static::areVisible().
     *
     * @see https://laravel.com/docs/5.3/eloquent#query-scopes
     *
     * @param Builder $query the query to modify
     */
    public function scopeAreVisible(Builder $query): void
    {
        $query->where('visibility', '=', 'visible');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * Gets fee type from snake case to human text
     *
     * @deprecated use title property
     *
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        return $this->title;
    }
}
