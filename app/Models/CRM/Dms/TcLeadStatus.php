<?php


namespace App\Models\CRM\Dms;

use App\Models\CRM\Leads\Lead;
use Illuminate\Database\Eloquent\Model;

class TcLeadStatus extends Model
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
        return $this->hasMany(Lead::class, 'website_id', 'id');
    }
}
