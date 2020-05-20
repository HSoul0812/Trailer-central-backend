<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class CycleCountHistory extends Model {
    
    protected $table = 'parts_cycle_count_history';

    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cycle_count_id',
        'part_id',
        'count_on_hand',
        'starting_qty'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

}
