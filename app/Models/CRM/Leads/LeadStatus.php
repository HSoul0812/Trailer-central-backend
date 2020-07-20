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

    /**
     * Update Next Contact Date
     */
    public function updateStatus($newStatus) {
        // Set New Status
        return $this->update(['status' => $newStatus]);
    }

    /**
     * Update Next Contact Date
     */
    public function updateNextContactDate($nextContactDate = null) {
        // No Next Contact Date?
        if($nextContactDate === null) {
            // Initialize Next Contact Date
            $datetime = time();

            // Get Current Hour/Minute/Month
            $curHr = date("G", $datetime);
            $curMn = date("i", $datetime);
            $curMo = date("n", $datetime);

            // Increment Day By One!
            $nextDay = date("d") + 1;

            // Make Next Contact Time
            $nextContactTime = mktime($curHr, $curMn, 0, $curMo, $nextDay);

            // Convert to GM Date Time
            $nextContactDate = gmdate("Y:m:d H:i:s", $nextContactTime);
        }

        // Save
        return $this->update(['next_contact_date' => $nextContactDate]);
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}