<?php

namespace App\Models\Parts;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User\DealerLocation;
/**
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 */
class Bin extends Model
{
    use TableAware;

    protected $table = 'dms_settings_part_bin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'location',
        'bin_name'
    ];

    public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function uncompletedCycleCounts()
    {
        return $this->hasMany(CycleCount::class)->where('is_completed', 0)->with('parts');
    }
    
    public function dealerLocation() : HasOne
    {
        return $this->hasOne(DealerLocation::class, 'dealer_location_id', 'location');
    }
}
