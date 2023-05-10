<?php

namespace App\Models\CRM\Email;

use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Email\BlastBrand;
use App\Models\CRM\Email\BlastCategory;
use App\Models\CRM\Email\BlastSent;
use App\Models\CRM\Email\Bounce;
use App\Models\Inventory\Inventory;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\CRM\Email\DTOs\BlastStats;

/**
 * Class Email Blast
 *
 * @package App\Models\CRM\Email
 */
class Blast extends Model
{
    use TableAware;

    // Define Constants for Actions
    const ACTION_INQUIRED = 'inquired';
    const ACTION_PURCHASED = 'purchased';
    const ACTION_UNCONTACTED = 'uncontacted';
    const ACTION_CONTACTED = 'contacted';

    // Define Constants to Make it Easier to Autocomplete
    const STATUS_ACTIONS = [
        self::ACTION_INQUIRED,
        self::ACTION_PURCHASED,
        self::ACTION_UNCONTACTED,
        self::ACTION_CONTACTED
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
    protected $table = 'crm_email_blasts';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'email_blasts_id';

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
        'delivered',
        'cancelled',
        'send_date',
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
    public function brands(): HasMany
    {
        return $this->hasMany(BlastBrand::class, 'email_blast_id');
    }

    /**
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(BlastCategory::class, 'email_blast_id');
    }

    /**
     * @return HasMany
     */
    public function sents()
    {
        return $this->hasMany(BlastSent::class, 'email_blasts_id');
    }

    /**
     * @return belongsTo
     */
    public function factory()
    {
        return $this->belongsTo(CampaignFactory::class, 'blast_id');
    }


    /**
     * Get Lead ID's for Text Blast
     *
     * @return array<int>
     */
    public function getLeadIdsAttribute()
    {
        // Get Leads for Blast
        $blast = $this;
        $query = $this->leadsBase();

        // Is Archived?!
        if($blast->include_archived === '-1') {
            $query = $query->where(Lead::getTableName() . '.is_archived', 0);
        } elseif($blast->include_archived !== '0') {
            $query = $query->where(Lead::getTableName() . '.is_archived', $blast->include_archived);
        }

        // Get Categories
        if(!empty($blast->categories)) {
            $categories = array();
            foreach($blast->categories as $category) {
                $categories[] = $category->unit_category;
            }

            // Add IN
            if(count($categories) > 0) {
                $query->where(function (Builder $query) use ($categories) {
                    $query->whereIn(Inventory::getTableName() . '.category', $categories)
                          ->orWhereIn('unit.category', $categories);
                });
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
                $query->where(function (Builder $query) use ($brands) {
                    $query->whereIn(Inventory::getTableName() . '.manufacturer', $brands)
                          ->orWhereIn('unit.manufacturer', $brands);
                });
            }
        }

        // Valid Status?
        switch($blast->action) {
            case self::ACTION_INQUIRED:
                $query = $query->where(function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where(LeadStatus::getTableName() . '.status', '<>', Lead::STATUS_WON)
                              ->where(LeadStatus::getTableName() . '.status', '<>', Lead::STATUS_WON_CLOSED);
                    })->orWhere(LeadStatus::getTableName() . '.status', NULL);
                });
            break;
            case self::ACTION_PURCHASED:
                $query = $query->where(function (Builder $query) {
                    $query->where(LeadStatus::getTableName() . '.status', Lead::STATUS_WON)
                          ->orWhere(LeadStatus::getTableName() . '.status', Lead::STATUS_WON_CLOSED);
                });
            break;
            case self::ACTION_UNCONTACTED:
                $query = $query->where(function (Builder $query) {
                    $query->where(LeadStatus::getTableName() . '.status', Lead::STATUS_UNCONTACTED)
                          ->orWhere(LeadStatus::getTableName() . '.status', 'open')
                          ->orWhere(LeadStatus::getTableName() . '.status', '')
                          ->orWhereNull(LeadStatus::getTableName() . '.status');
                });
            break;
            case self::ACTION_CONTACTED:
                $query = $query->where(function (Builder $query) {
                    $query->where(LeadStatus::getTableName() . '.status', '<>', Lead::STATUS_WON)
                          ->where(LeadStatus::getTableName() . '.status', '<>', Lead::STATUS_WON_CLOSED)
                          ->where(LeadStatus::getTableName() . '.status', '<>', Lead::STATUS_LOST)
                          ->where(LeadStatus::getTableName() . '.status', '<>', Lead::STATUS_UNCONTACTED);
                });
            break;
        }

        // Add Location to Query!
        if(!empty($blast->location_id)) {
            $query = $query->where(function (Builder $query) use($blast) {
                return $query->where(Lead::getTableName() . '.dealer_location_id', $blast->location_id)
                             ->orWhereRaw('(' . Lead::getTableName() . '.dealer_location_id = 0 AND ' .
                                            Inventory::getTableName() . '.dealer_location_id = ?)',
                                            [$blast->location_id]);
            });
        }

        // Append Remaining Requirements and Return Result
        return $query->whereNull(BlastSent::getTableName() . '.email_blasts_id')
                     ->whereRaw('DATE_ADD(' . Lead::getTableName() . '.date_submitted, ' .
                                'INTERVAL ' . $blast->send_after_days . ' DAY) >= NOW()')
                     ->pluck('identifier');
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
        return Lead::select(Lead::getTableName() . '.*')
                   ->leftJoin(Inventory::getTableName(), Lead::getTableName() . '.inventory_id',
                                '=', Inventory::getTableName() . '.inventory_id')
                   ->leftJoin(InventoryLead::getTableName(), Lead::getTableName() . '.identifier',
                                '=', InventoryLead::getTableName() . '.website_lead_id')
                   ->leftJoin(Inventory::getTableName() . ' as unit', 'unit.inventory_id',
                                '=', InventoryLead::getTableName() . '.inventory_id')
                   ->leftJoin(BlastSent::getTableName(), function($join) use($blast) {
                        return $join->on(BlastSent::getTableName() . '.lead_id',
                                            '=', Lead::getTableName() . '.identifier')
                                    ->where(BlastSent::getTableName() . '.email_blasts_id', '=', $blast->id);
                   })
                   ->leftJoin(LeadStatus::getTableName(), Lead::getTableName() . '.identifier',
                                '=', LeadStatus::getTableName() . '.tc_lead_identifier')
                   ->where(Lead::getTableName() . '.lead_type', '<>', LeadType::TYPE_NONLEAD)
                   ->where(Lead::getTableName() . '.dealer_id', $blast->newDealerUser->id)
                   ->where(Lead::getTableName() . '.email_address', '<>', '')
                   ->whereNotNull(Lead::getTableName() . '.email_address')
                   ->groupBy(Lead::getTableName() . '.identifier');
    }

    /**
     * Get Status for Email Blast
     *
     * @return BlastStats
     */
    public function getStatsAttribute(): BlastStats
    {
        // Get Stats for Blast
        return new BlastStats([
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
