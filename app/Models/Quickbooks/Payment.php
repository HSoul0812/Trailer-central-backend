<?php

namespace App\Models\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class Payment extends Model
{ 
    protected $table = 'qb_payment';

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

    public function invoice()
    {
        return $this->belongsTo('App\Models\Quickbooks\Invoice');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\Models\Quickbooks\PaymentMethod');
    }

}
