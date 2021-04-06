<?php

namespace App\Models\Website\Tracking;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Website Tracking Unit
 *
 * @package App\Models\Website\Tracking
 */
class TrackingUnit extends Model
{
    /**
     * @const string
     */
    const DEFAULT_UNIT_TYPE = 'inventory';

    /**
     * @const array
     */
    const VALID_UNIT_TYPES = [
        'inventory',
        'part',
        'showroom'
    ];


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_tracking_units';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'tracking_unit_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'inventory_id',
        'type',
        'date_viewed',
        'referrer',
        'path',
        'inquired'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_viewed';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * @return HasMany
     */
    public function unit()
    {
        return $this->belongsTo(Tracking::class, 'session_id', 'session_id');
    }

    /**
     * @return HasOne
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
