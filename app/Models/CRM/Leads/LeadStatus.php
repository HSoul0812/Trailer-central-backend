<?php


namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_tc_lead_status';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'identifier', 'tc_lead_identifier');
    }
}
