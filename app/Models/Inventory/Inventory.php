<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{ 
    protected $table = 'inventory';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
    
    public function floorplanPayments()
    {
        return $this->hasMany('App\Models\Inventory\Floorplan\Payment');
    }
    
    public function __toString() {
        return $this->title;
    }
}
