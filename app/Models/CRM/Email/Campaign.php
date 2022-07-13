<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Email Campaign
 *
 * @package App\Models\CRM\Email
 */
class Campaign extends Model
{

    // Define Constants to Make it Easier to Autocomplete
    const STATUS_ACTIONS = [
        'inquired',
        'purchased',
        'uncontacted',
        'contacted'
    ];

    const STATUS_ARCHIVED = [
        '0',
        '-1',
        '1'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_drip_campaigns';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'drip_campaigns_id';

    /**
     * Enable Timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'email_template_id',
        'campaign_name',
        'campaign_subject',
        'from_email_address',
        'action',
        'location_id',
        'send_after_days',
        'include_archived',
        'is_enabled'
    ];

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

    /**
     * Get Template
     * 
     * @return BelongsTo
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'email_template_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function brands()
    {
        return $this->hasMany(CampaignBrand::class, 'email_campaign_id');
    }

    /**
     * @return HasMany
     */
    public function categories()
    {
        return $this->hasMany(CampaignCategory::class, 'email_campaign_id');
    }

    /**
     * @return belongsTo
     */
    public function factory()
    {
        return $this->belongsTo(CampaignFactory::class, 'drip_campaigns_id');
    }
}