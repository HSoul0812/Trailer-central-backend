<?php

namespace App\Models\Website\Tracking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Website Tracking
 *
 * @package App\Models\Website\Tracking
 */
class Tracking extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_tracking';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'tracking_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'lead_id',
        'referrer',
        'domain',
        'date_inquired'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * Belongs To Lead
     * 
     * @return BelongsTo<Lead>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'identifier', 'lead_id');
    }

    /**
     * Has Many TrackingUnit
     * 
     * @return HasMany<TrackingUnit>
     */
    public function units(): HasMany
    {
        return $this->hasMany(TrackingUnit::class, 'session_id', 'session_id');
    }
}
