<?php


namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

class LeadType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_lead_types';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'lead_type_id';

    /**
     * Get lead.
     */
    public function lead()
    {
        return $this->hasOne(LeadType::class, 'identifier', 'lead_id');
    }
}
