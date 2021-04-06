<?php

namespace App\Models\Website\Tracking;

use Illuminate\Database\Eloquent\Model;

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
        'date_created',
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
     * @return BelongsTo
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'identifier', 'lead_id');
    }

    /**
     * @return HasMany
     */
    public function units()
    {
        return $this->hasMany(TrackingUnit::class, 'session_id', 'session_id');
    }
}
