<?php

namespace App\Models\CRM\Dms;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\Customer;


class UnitSale extends Model
{

    const QuoteStatus = [
        'OPEN' => 'open',
        'DEAL' => 'deal',
        'COMPLETED' => 'completed_deal',
        'ARCHIVED' => 'archived',
    ];

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
        return $this->belongsTo(Customer::class, 'buyer_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'identifier', 'lead_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'unit_sale_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'unit_sale_id');
    }

    public function getPaidAmountAttribute()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'unit_sale_id')->sum('amount');
    }

    public function getStatusAttribute() {
        if (!empty($this->is_archived)) {
            return 'Archived';
        }
        if (empty($this->paid_amount)) {
            return 'Open';
        }

        $balance = (float) $this->total_price - (float) $this->paid_amount;
        if ($balance > 0) {
            return 'Deal';
        }
        return 'Completed Deal';
    }
}
