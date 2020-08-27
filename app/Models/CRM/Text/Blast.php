<?php

namespace App\Models\CRM\Text;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Leads\Lead;

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


    /**
     * Get Leads for Blast
     * 
     * @return Collection of Leads
     */
    public function getLeadsAttribute()
    {
        // Initialize Blast
        $blast = $this;

        // Find Filtered Leads
        $query = Lead::select('website_lead.*')
                     ->leftJoin('inventory', 'website_lead.inventory_id', '=', 'inventory.inventory_id')
                     ->leftJoin('crm_text_blast_sent', function($join) use($blast) {
                        return $join->on('crm_text_blast_sent.lead_id', '=', 'website_lead.identifier')
                                    ->where('crm_text_blast_sent.text_blast_id', '=', $blast->id);
                     })
                     ->leftJoin('crm_tc_lead_status', 'website_lead.identifier', '=', 'crm_tc_lead_status.tc_lead_identifier')
                     ->leftJoin('crm_text_stop', function($join) {
                        return $join->on(DB::raw("CONCAT('+1', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 10))"), '=', 'crm_text_stop.sms_number')
                                    ->orOn(DB::raw("CONCAT('+', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 11))"), '=', 'crm_text_stop.sms_number');
                     })
                     ->where('website_lead.dealer_id', $blast->newDealerUser->id)
                     ->where('website_lead.phone_number', '<>', '')
                     ->whereNotNull('website_lead.phone_number')
                     ->whereNull('crm_text_stop.sms_number')
                     ->whereNull('crm_text_blast_sent.text_blast_id');

        // Is Archived?!
        if($blast->included_archived === -1 || $blast->include_archived === '-1') {
            $query = $query->where('website_lead.is_archived', 0);
        } elseif($blast->included_archived !== 0 && $blast->include_archived === '0') {
            $query = $query->where('website_lead.is_archived', $blast->include_archived);
        }

        // Get Categories
        if(!empty($blast->categories)) {
            $categories = array();
            foreach($blast->categories as $category) {
                $categories[] = $category->category;
            }

            // Add IN
            if(count($categories) > 0) {
                $query = $query->whereIn('inventory.category', $categories);
            }
        }

        // Get Brands
        if(!empty($blast->brands)) {
            $brands = array();
            foreach($blast->brands as $brand) {
                $brands[] = $brand->brand;
            }

            // Add IN
            if(count($brands) > 0) {
                $query = $query->whereIn('inventory.manufacturer', $brands);
            }
        }
        
        // Toggle Action
        if($blast->action === 'purchased') {
            $query = $query->where(function (Builder $query) {
                $query->where('crm_tc_lead_status.status', Lead::STATUS_WON)
                      ->orWhere('crm_tc_lead_status.status', Lead::STATUS_WON_CLOSED);
            });
        } else {
            $query = $query->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON)
                           ->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON_CLOSED);
        }

        // Add Location to Query!
        if(!empty($blast->location_id)) {
            $query = $query->where(function (Builder $query) use($blast) {
                return $query->where('website_lead.dealer_location_id', $blast->location_id)
                             ->orWhereRaw('(website_lead.dealer_location_id = 0 AND inventory.dealer_location_id = ?)', [$blast->location_id]);
            });
        }

        // Return Filtered Query
        $query = $query->whereRaw('DATE_ADD(website_lead.date_submitted, INTERVAL +' . $blast->send_after_days . ' DAY) >= NOW()')->get();
        echo $query->toSql() . PHP_EOL . PHP_EOL;
        var_dump($blast);
        die;
    }
}