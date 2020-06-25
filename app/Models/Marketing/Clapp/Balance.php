<?php

namespace App\Models\Marketing\Clapp;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clapp_balance';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dealer_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'balance'
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
