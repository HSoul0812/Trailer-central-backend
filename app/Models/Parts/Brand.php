<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{ 
    protected $table = 'part_brands';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
