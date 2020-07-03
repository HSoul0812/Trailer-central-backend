<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Campaign
 *
 * @package App\Models\CRM\Text
 */
class Campaign extends Model
{
    protected $table = 'crm_text_campaign';

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
        'unit_category',
        'include_archived',
        'is_enabled',
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
        return $this->hasMany(CampaignBrand::class, 'text_campaign_id');
    }

    /**
     * @return type
     */
    public function categories()
    {
        return $this->hasMany(CampaignCategory::class, 'text_campaign_id');
    }

    /**
     * @return type
     */
    public function sent()
    {
        return $this->hasOne(CampaignSent::class, 'text_campaign_id');
    }
}