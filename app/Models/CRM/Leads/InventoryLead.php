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
}
