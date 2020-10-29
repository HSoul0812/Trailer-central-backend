<?php

namespace App\Models\CRM\Interactions;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\User\NewUser;

class Interaction extends Model
{
    use TableAware;

    const TABLE_NAME = 'crm_interaction';

    /**
     * @const array
     */
    const INTERACTION_TYPES = [
        'PHONE',
        'EMAIL',
        'IN PERSON',
        'INQUIRY',
        'CAMPAIGN',
        'BLAST',
        'CONTACT',
        'TASK',
        'CHAT'
    ];

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
    protected $primaryKey = 'interaction_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "lead_product_id",
        "tc_lead_id",
        "user_id",
        "interaction_type",
        "interaction_notes",
        "interaction_time"
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the email history for the interaction.
     */
    public function emailHistory()
    {
        return $this->hasMany(EmailHistory::class, 'interaction_id', 'interaction_id');
    }

    /**
     * Get the Lead for the interaction.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'tc_lead_id', 'identifier');
    }
    
    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class, 'tc_lead_id', 'tc_lead_identifier');
    }
    
    public function newUser()
    {
        return $this->belongsTo(NewUser::class, 'user_id', 'user_id');
    }
    
    public function getRealUsernameAttribute() 
    {
       if (!empty($this->sent_by)) {
           return $this->sent_by;
       }
                      
       if (!empty($this->from_email)) {
           return $this->from_email;
       }
       
       return $this->newUser->username;                        
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
