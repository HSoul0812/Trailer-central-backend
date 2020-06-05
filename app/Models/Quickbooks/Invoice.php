<?php

namespace App\Models\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class Invoice extends Model
{ 
    protected $table = 'qb_invoices';

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

    public function payments()
    {
        return $this->hasMany('App\Models\Quickbooks\Payment');
    }

}
