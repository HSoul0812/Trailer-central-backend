<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parts\CycleCountHistory;

/**
 * @author Marcel
 * @property Collection<CycleCountHistory> $parts
 */
class CycleCount extends Model {

    protected $table = 'parts_cycle_count';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'bin_id',
        'count_date',
        'is_completed',
        'is_balanced'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function parts()
    {
        return $this->hasMany(CycleCountHistory::class);
    }

}
