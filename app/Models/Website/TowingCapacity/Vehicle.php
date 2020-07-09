<?php

namespace App\Models\Website\TowingCapacity;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Vehicle
 * @package App\Models\Website\TowingCapacity
 */
class Vehicle extends Model
{
    protected $table = 'towing_capacity_vehicles';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'year',
        'make_id',
        'model',
        'sub_model',
        'drive_train',
        'engine',
        'tow_limit',
        'tow_type',
        'transmission',
        'gear_ratio',
        'towing_package_required',
        'payload_package_required',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function make()
    {
        return $this->belongsTo(Make::class);
    }
}
