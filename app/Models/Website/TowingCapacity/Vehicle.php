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
        'towing_capacity_make_id',
        'model',
        'sub_model',
        'drive_train',
        'engine',
        'tow_limit',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function make()
    {
        return $this->belongsTo(Make::class);
    }
}
