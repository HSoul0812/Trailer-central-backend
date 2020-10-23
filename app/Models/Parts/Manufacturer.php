<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{ 
    protected $table = 'part_manufacturers';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'postalcode',
        'handling_fee'
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
        return $this->hasMany('App\Models\Parts\Part');
    }
    
    public function __toString() {
        return $this->name;
    }
}
