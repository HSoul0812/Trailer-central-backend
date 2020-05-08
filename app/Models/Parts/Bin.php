<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class Bin extends Model {
    
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

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

}
