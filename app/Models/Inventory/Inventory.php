<?php


namespace App\Models\Inventory;

use App\Models\CRM\Dealer\DealerLocation;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'inventory_id';

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'inventory_id', 'inventory_id', InventoryLead::class);
    }

    public function attribute()
    {
        return $this->hasManyThrough(Attribute::class, 'eav_attribute_value', 'attribute_id', 'inventory_id');
    }

    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }
}
