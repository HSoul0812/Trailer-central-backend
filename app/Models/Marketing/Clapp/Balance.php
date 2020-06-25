<?php

namespace App\Models\Marketing\Clapp;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model {
    
    protected $table = 'clapp_balance';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'balance',
        'last_updated'
    ];
    
    public $timestamps = false;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
