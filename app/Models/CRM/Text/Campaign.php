<?php

namespace App\Models\CRM\Text;

use App\Models\Traits\TableAware;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;
use App\Services\CRM\Text\DTOs\CampaignStats;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Text Campaign
 *
 * @package App\Models\CRM\Text
 */
class Campaign extends Model
{
    use TableAware;

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
        'from_sms_number',
        'action',
        'location_id',
        'send_after_days',
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

    /**
     * @return HasMany
     */
    public function success(): HasMany
    {
        return $this->sent()->whereIn('status', CampaignSent::STATUS_SUCCESS);
    }

    /**
     * @return HasMany
     */
    public function failed(): HasMany
    {
        return $this->sent()->whereIn('status', CampaignSent::STATUS_FAILED);
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


    /**
     * Get Cleaned Include Archived Status
     * 
     * @return int version of include_archived
     */
    public function getArchivedStatusAttribute()
    {
        return (int) $this->include_archived;
    }


    /**
     * Get Leads for Campaign
     * 
     * @return Collection of Leads
     */
    public function getLeadsAttribute()
    {
        // Get Leads for Campaign
        return $this->leadsBase()
                    ->whereNull(Stop::getTableName() . '.sms_number')
                    ->whereNull('crm_text_campaign_sent.text_campaign_id')
                    ->get();
    }


    /**
     * Get Status for Text Campaign
     * 
     * @return CampaignStats
     */
    public function getStatsAttribute(): CampaignStats
    {
        // Get Leads for Campaign
        return new CampaignStats([
            'skipped' => $this->skipped,
            'sent' => $this->success,
            'failed' => $this->failed,
            'unsubscribed' => $this->unsubscribed
        ]);
    }

    /**
     * Get Skipped Leads for Campaign
     * 
     * @return int
     */
    public function getSkippedAttribute(): int
    {
        // Get Leads for Campaign
        return $this->leadsBase()
                    ->whereNotIn(CampaignSent::getTableName() . '.status', CampaignSent::STATUS_SUCCESS)
                    ->count();
    }

    /**
     * Get Unsubscribed Leads for Campaign
     * 
     * @return int
     */
    public function getUnsubscribedAttribute(): int
    {
        // Get Leads for Campaign
        return $this->leadsBase()
                    ->whereNotNull('crm_text_campaign_sent.text_campaign_id')
                    ->count();
    }


    /**
     * Get Leads Template
     */
    private function leadsBase(): Builder {
        // Initialize Campaign
        $campaign = $this;

        // Find Filtered Leads
        $query = Lead::select('website_lead.*')
                     ->leftJoin('inventory', 'website_lead.inventory_id', '=', 'inventory.inventory_id')
                     ->leftJoin('crm_text_campaign_sent', function($join) use($campaign) {
                        return $join->on('crm_text_campaign_sent.lead_id', '=', 'website_lead.identifier')
                                    ->where('crm_text_campaign_sent.text_campaign_id', '=', $campaign->id);
                     })
                     ->leftJoin('crm_tc_lead_status', 'website_lead.identifier', '=', 'crm_tc_lead_status.tc_lead_identifier')
                     ->leftJoin(Stop::getTableName(), function($join) {
                        return $join->on(DB::raw("CONCAT('+1', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 10))"), '=', Stop::getTableName() . '.sms_number')
                                    ->orOn(DB::raw("CONCAT('+', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 11))"), '=', Stop::getTableName() . '.sms_number');
                     })
                     ->where('website_lead.lead_type', '<>', LeadType::TYPE_NONLEAD)
                     ->where('website_lead.dealer_id', $campaign->newDealerUser->id)
                     ->where('website_lead.phone_number', '<>', '')
                     ->whereNotNull('website_lead.phone_number');

        // Is Archived?!
        if($campaign->archived_status === -1) {
            $query = $query->where('website_lead.is_archived', 0);
        } elseif($campaign->archived_status !== 0) {
            $query = $query->where('website_lead.is_archived', $campaign->archived_status);
        }

        // Get Categories
        if(!empty($campaign->categories)) {
            $categories = array();
            foreach($campaign->categories as $category) {
                $categories[] = $category->category;
            }

            // Add IN
            if(count($categories) > 0) {
                $query = $query->whereIn('inventory.category', $categories);
            }
        }

        // Get Brands
        if(!empty($campaign->brands)) {
            $brands = array();
            foreach($campaign->brands as $brand) {
                $brands[] = $brand->brand;
            }

            // Add IN
            if(count($brands) > 0) {
                $query = $query->whereIn('inventory.manufacturer', $brands);
            }
        }
        
        // Toggle Action
        if($campaign->action === 'purchased') {
            $query = $query->where(function (Builder $query) {
                $query->where('crm_tc_lead_status.status', Lead::STATUS_WON)
                      ->orWhere('crm_tc_lead_status.status', Lead::STATUS_WON_CLOSED);
            });
        } else {
            $query = $query->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON)
                          ->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON_CLOSED);
                })->orWhere('crm_tc_lead_status.status', NULL);
            });
        }

        // Add Location to Query!
        if(!empty($campaign->location_id)) {
            $query = $query->where(function (Builder $query) use($campaign) {
                return $query->where('website_lead.dealer_location_id', $campaign->location_id)
                             ->orWhereRaw('(website_lead.dealer_location_id = 0 AND inventory.dealer_location_id = ?)', [$campaign->location_id]);
            });
        }

        // Return Filtered Query
        return $query->whereRaw('DATE_ADD(website_lead.date_submitted, INTERVAL +' . $campaign->send_after_days . ' DAY) < NOW()')
                     ->whereRaw('(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(website_lead.date_submitted)) / (60 * 60 * 24)) - ' . $campaign->send_after_days . ') <= 10');
    }
}