<?php

namespace App\Models\Quickbooks;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{ 
    protected $table = 'dms_customer';

    public $timestamps = false;
    
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

}
