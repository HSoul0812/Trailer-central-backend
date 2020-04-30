<?php


namespace App\Models\CRM\Product;

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
        return $this->belongsTo(Lead::class, 'inventory_id', 'inventory_id', 'crm_inventory_lead');
    }
}
