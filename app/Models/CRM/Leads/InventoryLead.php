<?php
namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

class InventoryLead extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_inventory_lead';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
