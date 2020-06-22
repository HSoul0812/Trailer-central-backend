<?php


namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;

class LeadStatus extends Model
{
    
    use TableAware;
    
    const TABLE_NAME = 'crm_tc_lead_status';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;
    
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
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
