<?php

namespace App\Models\CRM\Dms;

use App\Models\CRM\User\SalesPerson;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\Customer;


/**
 * Class UnitSale
 * @package App\Models\CRM\Dms
 * @property Customer $customer
 * @property SalesPerson $salesPerson
 */
class UnitSale extends Model implements GenericSaleInterface
{

    const QUOTE_STATUS_OPEN = 'open';
    const QUOTE_STATUS_DEAL = 'deal';
    const QUOTE_STATUS_COMPLETED = 'completed_deal';
    const QUOTE_STATUS_ARCHIVED = 'archived';

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
        return $this->belongsTo(Customer::class, 'buyer_id', 'id');
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

    public function salesPerson()
    {
        return $this->hasOne(SalesPerson::class, 'id', '');
    }

    public function dealerCost()
    {
        // TODO: Implement dealerCost() method.
    }

    public function subtotal()
    {
        // TODO: Implement subtotal() method.
    }

    public function discount()
    {
        // TODO: Implement discount() method.
    }

    public function taxTotal()
    {
        // TODO: Implement taxTotal() method.
    }

    public function createdAt()
    {
        return $this->createdAt();
    }
}
