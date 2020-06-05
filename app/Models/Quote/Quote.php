<?php

namespace App\Models\Quote;

use Illuminate\Database\Eloquent\Model;

class QuoteStatus
{
    const OPEN = 'open';
    const DEAL = 'deal';
    const COMPLETED = 'completed_deal';
    const ARCHIVED = 'archived';
}

class Quote extends Model
{ 
    protected $table = 'dms_unit_sale';

    protected $appends = ['paid_amount'];

    const UPDATED_AT = null;
    
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

    public function customer()
    {
        return $this->belongsTo('App\Models\Quickbooks\Customer', 'buyer_id');
    }

    public function invoice()
    {
        return $this->hasOne('App\Models\Quickbooks\Invoice', 'unit_sale_id');
    }

    public function payments()
    {
        return $this->hasManyThrough('App\Models\Quickbooks\Payment', 'App\Models\Quickbooks\Invoice', 'unit_sale_id');
    }

    public function getPaidAmountAttribute()
    {
        return $this->hasManyThrough('App\Models\Quickbooks\Payment', 'App\Models\Quickbooks\Invoice', 'unit_sale_id')->sum('amount');
    }

}
