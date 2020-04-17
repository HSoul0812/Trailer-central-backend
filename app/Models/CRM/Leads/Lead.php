<?php

namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_lead';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'lead_id';

    public function customer()
    {
        return $this->hasOne(Customer::class, 'customer_id', 'customer_id');
    }
}
