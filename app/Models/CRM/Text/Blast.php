<?php

namespace App\Models\CRM\Text;

use App\Models\Traits\TableAware;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;
use App\Services\CRM\Text\DTOs\BlastStats;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Text Blast
 *
 * @package App\Models\CRM\Text
 */
class Blast extends Model
{
    use TableAware;

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
        'from_sms_number',
        'action',
        'location_id',
        'send_after_days',
        'include_archived',
        'is_delivered',
        'is_cancelled',
        'is_error',
        'send_date',
        'deleted',
        'log',
    ];

    protected $casts = [
        'is_error' => 'bool',
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
     * @return HasMany
     */
    public function sent(): HasMany
    {
        return $this->hasMany(BlastSent::class, 'text_blast_id');
    }

    /**
     * @return HasMany
     */
    public function success(): HasMany
    {
        return $this->sent()->whereIn('status', BlastSent::STATUS_SUCCESS);
    }

    /**
     * @return HasMany
     */
    public function failed(): HasMany
    {
        return $this->sent()->whereIn('status', BlastSent::STATUS_FAILED);
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
     * Get Leads for Text Blast
     *
     * @return Collection<Lead>
     */
    public function getLeadsAttribute()
    {
        // Get Leads for Blast
        $blast = $this;
        $query = $this->leadsBase();

        // Is Archived?!
        if($blast->archived_status === -1) {
            $query = $query->where('website_lead.is_archived', 0);
        } elseif($blast->archived_status !== 0) {
            $query = $query->where('website_lead.is_archived', $blast->archived_status);
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
            $query = $query->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON)
                          ->where('crm_tc_lead_status.status', '<>', Lead::STATUS_WON_CLOSED);
                })->orWhere('crm_tc_lead_status.status', NULL);
            });
        }

        // Add Location to Query!
        if(!empty($blast->location_id)) {
            $query = $query->where(function (Builder $query) use($blast) {
                return $query->where('website_lead.dealer_location_id', $blast->location_id)
                             ->orWhereRaw('(website_lead.dealer_location_id = 0 AND inventory.dealer_location_id = ?)', [$blast->location_id]);
            });
        }

        // Append Remaining Requirements and Return Result
        return $query->whereNull(Stop::getTableName() . '.sms_number')
                     ->whereNull(BlastSent::getTableName() . '.text_blast_id')
                     ->whereRaw('DATE_ADD(website_lead.date_submitted, INTERVAL +' . $this->send_after_days . ' DAY) >= NOW()')
                     ->get();
    }


    /**
     * Get Status for Text Blast
     *
     * @return BlastStats
     */
    public function getStatsAttribute(): BlastStats
    {
        // Get Leads for Blast
        return new BlastStats([
            'sent' => $this->success->count(),
            'failed' => $this->failed->count(),
            'unsubscribed' => $this->unsubscribed
        ]);
    }

    /**
     * Get Skipped Leads for Text Blast
     *
     * @return int
     */
    public function getSkippedAttribute(): int
    {
        // Get Number of Skipped Leads on Blast
        return $this->leadsBase()
                    ->whereNotIn(BlastSent::getTableName() . '.status', BlastSent::STATUS_SUCCESS)
                    ->count();
    }

    /**
     * Get Unsubscribed Leads for Campaign
     *
     * @return int
     */
    public function getUnsubscribedAttribute(): int
    {
        // Get Number of Unsubscribed Leads on Blast
        return $this->leadsBase()
                    ->whereNotNull(BlastSent::getTableName() . '.text_blast_id')
                    ->where(Stop::getTableName() . '.type', Stop::REPORT_TYPE_DEFAULT)
                    ->count();
    }

    /**
     * Get Builder Object for Blast Leads
     *
     * @return Builder
     */
    private function leadsBase(): Builder {
        // Initialize Blast
        $blast = $this;

        // Find Filtered Leads
        return Lead::select('website_lead.*')
                   ->leftJoin('inventory', 'website_lead.inventory_id', '=', 'inventory.inventory_id')
                   ->leftJoin('crm_text_blast_sent', function($join) use($blast) {
                        return $join->on('crm_text_blast_sent.lead_id', '=', 'website_lead.identifier')
                                    ->where('crm_text_blast_sent.text_blast_id', '=', $blast->id);
                   })
                   ->leftJoin('crm_tc_lead_status', 'website_lead.identifier', '=', 'crm_tc_lead_status.tc_lead_identifier')
                   ->leftJoin(Stop::getTableName(), function($join) {
                        return $join->on(DB::raw("CONCAT('+1', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 10))"), '=', Stop::getTableName() . '.sms_number')
                                    ->orOn(DB::raw("CONCAT('+', SUBSTR(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(website_lead.phone_number, '(', ''), ')', ''), '-', ''), ' ', ''), '-', ''), '+', ''), '.', ''), 1, 11))"), '=', Stop::getTableName() . '.sms_number');
                   })
                   ->where('website_lead.lead_type', '<>', LeadType::TYPE_NONLEAD)
                   ->where('website_lead.dealer_id', $blast->newDealerUser->id)
                   ->where('website_lead.phone_number', '<>', '')
                   ->whereNotNull('website_lead.phone_number');
    }
}
