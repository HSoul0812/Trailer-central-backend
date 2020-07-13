<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;

/**
 * Class Text Blast
 *
 * @package App\Models\CRM\Text
 */
class Blast extends Model
{
    protected $table = 'crm_text_blast';

    // Define Constants to Make it Easier to Autocomplete
    const STATUS_ACTIONS = [
        'inquired',
        'purchased'
    ];

    const STATUS_ARCHIVED = [
        '0',
        '-1',
        '1'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'template_id',
        'campaign_name',
        'campaign_subject',
        'from_sms_number',
        'action',
        'location_id',
        'send_after_days',
        'include_archived',
        'is_delivered',
        'is_cancelled',
        'send_date',
        'deleted',
    ];

    /**
     * @return type
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @return type
     */
    public function brands()
    {
        return $this->hasMany(BlastBrand::class, 'text_blast_id');
    }

    /**
     * @return type
     */
    public function categories()
    {
        return $this->hasMany(BlastCategory::class, 'text_blast_id');
    }

    /**
     * @return type
     */
    public function sent()
    {
        return $this->hasOne(BlastSent::class, 'text_blast_id');
    }

    /**
     * Get CRM User
     */
    public function crmUser()
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get Dealer User
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'user_id', 'user_id');
    }
}