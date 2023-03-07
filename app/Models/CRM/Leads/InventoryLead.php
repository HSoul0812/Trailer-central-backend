<?php

namespace App\Models\CRM\Leads;

use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InventoryLead
 * @package App\Models\CRM\Leads
 *
 * @property int $id
 * @property int|null $crm_lead_id
 * @property int|null $website_lead_id
 * @property int $inventory_id
 *
 * @property Inventory $inventory
 * @property Lead $lead
 */
class InventoryLead extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_inventory_lead';

    /**
     * No timestamps on table
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'crm_lead_id',
        'website_lead_id',
        'inventory_id'
    ];

    /**
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    /**
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'website_lead_id', 'identifier');
    }
}
