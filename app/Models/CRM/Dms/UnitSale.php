<?php

namespace App\Models\CRM\Dms;

use App\Models\CRM\User\SalesPerson;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\Customer;
use App\Models\CRM\Dms\Quickbooks\PaymentMethod;
use App\Models\CRM\Dms\Quickbooks\Item;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\User;
use App\Models\CRM\Dms\UnitSale\TradeIn;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    
    public function dealer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'buyer_id', 'id');
    }
    
    public function coCustomer() : BelongsTo
    {
        return $this->belongsTo(Customer::class, 'cobuyer_id', 'id');
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
    
    public function tradeIn() : HasMany
    {
        return $this->hasMany(TradeIn::class, 'unit_sale_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
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
    
    public function getCostOfPrimaryVehicleAttribute() : float
    {
        $costOfVehicle = 0;
        if ( $this->invoice ) {
            foreach($this->invoice as $invoice) {
                foreach($invoice->items as $item) {
                    if ( $item->item->type === Item::ITEM_TYPES['TRAILER'] ) {
                        $costOfVehicle += $item->cost;
                    }
                }
            }
        }
        return $costOfVehicle;
    }
    
    public function amountFinanced() : float
    {
        $amountFinanced = 0;
        foreach($this->payments as $payment) {
            if( $payment->paymentMethod->type === PaymentMethod::PAYMENT_METHOD_FINANCING ) {
                $amountFinanced += $payment->amount;
            }
        }
        
        return $amountFinanced;
    }
    
    public function isFinanced() : bool
    {
        foreach($this->payments as $payment) {
            if( $payment->paymentMethod->type === PaymentMethod::PAYMENT_METHOD_FINANCING ) {
                return true;
            }
        }
        
        return false;
    } 

    public function taxTotal() : float
    {
        $taxAmount = 0;
        if ( $this->invoice ) {
            foreach($this->invoice as $invoice) {
                foreach($invoice->items as $item) {
                    if ( $item->item->type === Item::ITEM_TYPES['TAX'] ) {
                        $taxAmount += $item->itemPrice;
                    }
                }
            }            
        }
        
        return $taxAmount;
    }

    public function createdAt()
    {
        return $this->created_at;
    }
}
