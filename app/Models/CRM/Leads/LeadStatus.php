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

    const STATUS_WON = 'Closed';
    const STATUS_WON_CLOSED = 'Closed (Won)';
    const STATUS_LOST = 'Closed (Lost)';
    const STATUS_HOT = 'Hot';
    const STATUS_COLD = 'Cold';
    const STATUS_MEDIUM = 'Medium';
    const STATUS_UNCONTACTED = 'Uncontacted';
    const STATUS_NEW_INQUIRY = 'New Inquiry';

    const STATUS_ARRAY = [
        self::STATUS_WON,
        self::STATUS_WON_CLOSED,
        self::STATUS_LOST,
        self::STATUS_HOT,
        self::STATUS_COLD,
        self::STATUS_MEDIUM,
        self::STATUS_UNCONTACTED,
        self::STATUS_NEW_INQUIRY
    ];

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
        'tc_lead_identifier',
        'status',
        'source',
        'next_contact_date',
        'sales_person_id',
        'contact_type'
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