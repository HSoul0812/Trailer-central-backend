<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class VehicleSpecific extends Model
{ 
    protected $table = 'vehicle_specific';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'make',
        'model',
        'year_from',
        'year_to',
        'part_id'
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
        return $this->belongsTo('App\Models\Parts\Part');
    }
}
