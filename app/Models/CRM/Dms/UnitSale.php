<?php

namespace App\Models\CRM\Dms;

use App\Models\CRM\User\SalesPerson;
use App\Models\Traits\TableAware;
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
 * @property UnitSaleInventory[] $extraInventory
 */
class UnitSale extends Model implements GenericSaleInterface
{
    use TableAware;

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
        return $this->hasMany(Invoice::class, 'unit_sale_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'unit_sale_id');
    }

    public function extraInventory()
    {
        return $this->hasMany(UnitSaleInventory::class, 'quote_id', 'id');
    }

    public function getPaidAmountAttribute()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'unit_sale_id')->sum('amount');
    }

    public function getStatusAttribute() {
        if (!empty($this->is_archived)) {
            return 'Archived';
        }
        if ($this->is_po === 1) {
            return 'Completed Deal';
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
        // todo implement dealer cost. i.e. sum all the dealer cost of all
        //   included inventory, parts, etc
        throw new \Exception('Not implemented');
    }

    public function subtotal()
    {
        return $this->subtotal;
    }

    public function discount()
    {
        return $this->inventory_discount +
            $this->accessory_discount +
            $this->labor_discount;
    }

    public function taxTotal()
    {
        // todo implement total tax; need to create a service for server-side
        //   tax computation, otherwise you can only get taxes server-side after
        //   an invoice is made
        throw new \Exception('Not implemented');
    }

    public function createdAt()
    {
        return $this->created_at;
    }
}
