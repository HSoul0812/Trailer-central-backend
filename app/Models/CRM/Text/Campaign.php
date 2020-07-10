<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Leads\Lead;
use App\Models\User\CrmUser;

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
     * Get CRM user.
     */
    public function crmUser()
    {
        return $this->belongsTo(CrmUser::class, 'user_id', 'user_id');
    }

    /**
     * @return type
     */
    public function leads()
    {
        // Get Leads For Dealer
        return $this->hasManyThrough(Lead::class, CrmUser::class, 'user_id', 'dealer_id', 'user_id', 'id');
    }


    /**
     * Find Leads for Campaign
     * 
     * @return Collection of Leads
     */
    public static function findLeads($campaignId)
    {
        // Get Campaign
        $campaign = self::findOrFail($campaignId);

        // Find Filtered Leads
        return $campaign->leads()->where(function (Builder $query) use($campaign) {
            // Join Inventory Table
            $query = $query->leftJoin('inventory', 'website_lead.inventory_id', '=', 'inventory.inventory_id');

            // Is Archived?!
            if($campaign->included_archived !== -1) {
                $query = $query->where('is_archived', $campaign->include_archived);
            }

            // Get Categories
            if(!empty($campaign->categories)) {
                $categories = array();
                foreach($campaign->categories as $category) {
                    $categories[] = $category->category;
                }

                // Add IN
                $query = $query->whereIn('category', $categories);
            }

            // Get Brands
            if(!empty($campaign->brands)) {
                $brands = array();
                foreach($campaign->brands as $brand) {
                    $brands[] = $brand->brand;
                }

                // Add IN
                $query = $query->whereIn('manufacturer', $brands);
            }

            // Return Filtered Query
            return $query->where(function (Builder $query) use($campaign) {
                return $query->where('website_lead.dealer_location_id', $campaign->location_id)
                        ->orWhereRaw('(dealer_location_id = 0 AND inventory.dealer_location_id = ?)', [$campaign->location_id]);
            })->whereRaw('DATE_ADD(date_submitted, INTERVAL +' . $campaign->send_after_days . ' DAY) > NOW()');
        })->get();
    }
}