<?php

namespace App\Models\Feed\Mapping;

use App\Models\Traits\TableAware;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class ExternalDealerMapping extends Model
{
    use TableAware;

    protected $table = 'external_dealer_mapping';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'external_dealer_mapping_id';

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $fillable = [
        'external_dealer_mapping_id',
        'external_provider',
        'external_id',
        'dealer_id',
        'dealer_location_id',
        'is_active',
        'is_valid'
    ];

    /**
     * Get Dealer
     *
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Dealer Location
     *
     * @return BelongsTo
     */
    public function dealerLocation(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }
}
