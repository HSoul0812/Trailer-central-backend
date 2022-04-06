<?php

namespace App\Models\CRM\Dms;

use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Dms\ServiceOrder\MiscPartItem;
use App\Models\CRM\Dms\ServiceOrder\OtherItem;
use App\Models\CRM\Dms\ServiceOrder\PartItem;
use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use App\Models\User\DealerLocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceOrder
 *
 * @package App\Models\CRM\Dms
 *
 * @property boolean $closed_by_related_unit_sale
 * @property Collection<PartItem> $partItems
 * @property Collection<MiscPartItem> $miscPartItems
 * @property Collection<ServiceItem> $serviceItems
 * @property Collection<OtherItem> $otherItems
 * @property Invoice $invoice
 */
class ServiceOrder extends Model
{
    use TableAware;

    public const STATUS_ONLY_READY_FOR_PICK_UP = 'only_ready_for_pickup';
    public const STATUS_READY_FOR_PICK_UP = 'ready_for_pickup';
    public const STATUS_PICKED_UP = 'picked_up';

    const SERVICE_ORDER_STATUS = [
        self::STATUS_READY_FOR_PICK_UP => 'Closed / Picked Up',
        'closed_quote' => 'Closed / Quote',
        self::STATUS_PICKED_UP => 'Closed / Ready for Pickup',
        self::STATUS_ONLY_READY_FOR_PICK_UP => 'Ready for Pickup',
        'on_tech_clipboard' => 'On Tech Clipboard',
        'waiting_custom' => 'Waiting on Custom',
        'waiting_parts' => 'Waiting on Part(s)',
        'warranty_processing' => 'Warranty Processing',
        'quote' => 'Quote',
        'work_available' => 'Work Available',
    ];

    /*
     * RO statuses which consider it done.
     */
    const COMPLETED_ORDER_STATUS = [
        'picked_up',
        'ready_for_pickup',
        'closed_quote',
    ];

    public const TYPES = [
        self::TYPE_ESTIMATE,
        self::TYPE_INTERNAL,
        self::TYPE_RETAIL,
        self::TYPE_WARRANTY,
    ];

    public const TYPE_ESTIMATE = 'estimate';
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_RETAIL = 'retail';
    public const TYPE_WARRANTY = 'warranty';

    const SERVICE_ORDER_SCHEDULED = 'scheduled';
    const SERVICE_ORDER_COMPLETED = 'completed';
    const SERVICE_ORDER_NOT_COMPLETED = 'not_completed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms_repair_order';

    protected $fillable = [
        'status',
        'closed_at',
    ];

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

    public function partItems()
    {
        return $this->hasMany(PartItem::class, 'repair_order_id', 'id');
    }

    public function miscPartItems()
    {
        return $this->hasMany(MiscPartItem::class, 'repair_order_id', 'id');
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class, 'repair_order_id', 'id');
    }

    public function otherItems()
    {
        return $this->hasMany(OtherItem::class, 'repair_order_id', 'id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    public function getStatusNameAttribute(): string
    {
        return self::SERVICE_ORDER_STATUS[$this->status] ?? '';
    }
}
