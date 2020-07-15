<?php


namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;
use App\Models\CRM\User\SalesPerson;

class LeadStatus extends Model
{    
    use TableAware;
    
    const TYPE_CONTACT = 'CONTACT';
    const TYPE_TASK = 'TASK';
    
    const STATUS_UNCONTACTED = 'Uncontacted';
    
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
    
    public $timestamps = false;
    
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'tc_lead_identifier',
        'next_contact_date',
        'contact_type',
        'sales_person_id'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'identifier', 'tc_lead_identifier');
    }
    
    public function salesPerson()
    {
        return $this->belongsTo(SalesPerson::class, 'sales_person_id', 'id');
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
