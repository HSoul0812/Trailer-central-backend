<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\CRM\Email\DTOs\CampaignStats;

/**
 * Class Email Campaign
 *
 * @package App\Models\CRM\Email
 *
 * @property int $drip_campaigns_id
 * @property int $email_template_id
 * @property int|null $location_id
 * @property int $send_after_days
 * @property string $action
 * @property string|null $unit_category
 * @property string $campaign_name
 * @property int $user_id
 * @property string|null $from_email_address
 * @property string $campaign_subject
 * @property int|null $include_archived
 * @property bool|null $is_enabled
 *
 * @property CrmUser $crmUser
 * @property NewDealerUser $newDealerUser
 * @property Template $template
 * @property Collection<CampaignBrand> $brands
 * @property Collection<CampaignCategory> $categories
 * @property CampaignFactory $factory
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
    public function crmUser(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * Get Dealer User
     */
    public function newDealerUser(): BelongsTo
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
    public function brands(): HasMany
    {
        return $this->hasMany(CampaignBrand::class, 'email_campaign_id');
    }

    /**
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(CampaignCategory::class, 'email_campaign_id');
    }

    /**
     * @return belongsTo
     */
    public function factory(): BelongsTo
    {
        return $this->belongsTo(CampaignFactory::class, 'drip_campaigns_id');
    }

    /**
     * @return type
     */
    public function sents(): HasMany
    {
        return $this->hasMany(CampaignSent::class, 'drip_campaigns_id');
    }

    /**
     * Get Stats for Email Campaigns
     *
     * @return CampaignStats
     */
    public function getStatsAttribute(): CampaignStats
    {
        // Get Stats for Blast
        return new CampaignStats([
            'sent' => $this->sents->count(),
            'delivered' => $this->sents()->delivered()->count(),
            'bounced' => $this->sents()->bounced()->count(),
            'complained' => $this->sents()->complained()->count(),
            'unsubscribed' => $this->sents()->unsubscribed()->count(),
            'opened' => $this->sents()->opened()->count(),
            'clicked' => $this->sents()->clicked()->count(),
            'skipped' => $this->sents()->skipped()->count(),
        ]);
    }
}
