<?php

namespace App\Models\Website\TowingCapacity;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Make
 * @package App\Models\Website\TowingCapacity
 */
class Make extends Model
{
    protected $table = 'towing_capacity_makes';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
