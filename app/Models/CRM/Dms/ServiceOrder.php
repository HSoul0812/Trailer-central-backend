<?php

namespace App\Models\CRM\Dms;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\User\Customer;
use App\Models\User\DealerLocation;


class ServiceOrder extends Model
{

    const SERVICE_ORDER_STATUS = [
        'picked_up' => 'Closed / Picked Up',
        'ready_for_pickup' => 'Closed / Ready for Pickup',
        'on_tech_clipboard' => 'On Tech Clipboard',
        'waiting_custom' => 'Waiting on Custom',
        'waiting_parts' => 'Waiting on Part(s)',
        'warranty_processing' => 'Warranty Processing',
        'quote' => 'Quote',
        'work_available' => 'Work Available'
    ];

    const SERVICE_ORDER_ESTIMATE = 'estimate';
    const SERVICE_ORDER_SCHEDULED = 'scheduled';
    const SERVICE_ORDER_COMPLETED = 'completed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_repair_order';

    const UPDATED_AT = null;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'location', 'dealer_location_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'repair_order_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'repair_order_id');
    }

    public function getPaidAmountAttribute()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'repair_order_id')->sum('amount');
    }

}
