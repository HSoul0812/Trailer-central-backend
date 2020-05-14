<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class BinQuantity extends Model {
    
    protected $table = 'part_bin_qty';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id',
        'bin_id',
        'qty'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function bin()
    {
        return $this->hasOne('App\Models\Parts\Bin', 'id', 'bin_id');
    }
}
