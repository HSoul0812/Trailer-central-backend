<?php

namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

class LeadProduct extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_lead_product';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'lead_product_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "lead_id",
        "product_id",
    ];
}
