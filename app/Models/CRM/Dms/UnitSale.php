<?php

namespace App\Models\CRM\Dms;

use Illuminate\Database\Eloquent\Model;

class QuoteStatus
{
    const OPEN = 'open';
    const DEAL = 'deal';
    const COMPLETED = 'completed_deal';
    const ARCHIVED = 'archived';
}

class UnitSale extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_unit_sale';

    protected $appends = ['paid_amount'];

    const UPDATED_AT = null;

    public function customer()
    {
        return $this->belongsTo('App\Models\CRM\User\Customer', 'buyer_id');
    }

    public function lead()
    {
        return $this->belongsTo('App\Models\CRM\Leads\Lead', 'identifier', 'lead_id');
    }

    public function invoice()
    {
        return $this->hasOne('App\Models\CRM\Account\Invoice', 'unit_sale_id');
    }

    public function payments()
    {
        return $this->hasManyThrough('App\Models\CRM\Account\Payment', 'App\Models\CRM\Account\Invoice', 'unit_sale_id');
    }

    public function getPaidAmountAttribute()
    {
        return $this->hasManyThrough('App\Models\CRM\Account\Payment', 'App\Models\CRM\Account\Invoice', 'unit_sale_id')->sum('amount');
    }
}
