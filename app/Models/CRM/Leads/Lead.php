<?php

namespace App\Models\CRM\Leads;

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Product\Product;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\User\NewDealerUser;
use App\Models\User\CrmUser;
use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Traits\TableAware;
use App\Models\Website\Website;
use App\Models\Website\Tracking\Tracking;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\CRM\Leads\Facebook\Lead as FbLead;
use App\Models\CRM\Leads\Facebook\User as FbUser;

/**
 * Class Lead
 * @package App\Models\CRM\Leads
 *
 * @property int $identifier
 * @property int $website_id
 * @property string $lead_type
 * @property int $inventory_id
 * @property int $customer_id
 * @property int $ids_exported
 * @property string $referral
 * @property string $title
 * @property string $first_name
 * @property string $last_name
 * @property string $email_address
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $preferred_contact
 * @property string $phone_number
 * @property string $status
 * @property string $comments
 * @property \DateTimeInterface $next_followup
 * @property \DateTimeInterface $date_submitted
 * @property bool $is_spam
 * @property \DateTimeInterface $contact_email_sent
 * @property \DateTimeInterface $adf_email_sent
 * @property \DateTimeInterface $last_visited_at
 * @property bool $cdk_email_sent
 * @property string $metadata
 * @property bool $newsletter
 * @property string $note
 * @property bool $is_from_classifieds
 * @property int $dealer_id
 * @property int $dealer_location_id
 * @property bool $is_archived
 * @property int $unique_id
 * @property int $bigtex_exported
 *
 * @property User $user
 * @property Website $website
 * @property LeadStatus $leadStatus
 * @property FbUser $fbUsers
 * @property FbLead $fbLead
 * @property Inventory $inventory
 *
 * @property string $full_name
 *
 */
class Lead extends Model
{
    use TableAware;

    public const STATUS_WON = 'Closed';
    public const STATUS_WON_CLOSED = 'Closed (Won)';
    public const STATUS_LOST = 'Closed (Lost)';
    public const STATUS_HOT = 'Hot';
    public const STATUS_COLD = 'Cold';
    public const STATUS_MEDIUM = 'Medium';
    public const STATUS_UNCONTACTED = 'Uncontacted';
    public const STATUS_NEW_INQUIRY = 'New Inquiry';

    public const IGNORE_ARCHIVED = -1;
    public const NOT_ARCHIVED = 0;
    public const LEAD_ARCHIVED = 1;
    public const ARCHIVED_STATUSES = [
        self::NOT_ARCHIVED => 'Active Only',
        self::LEAD_ARCHIVED => 'Archived Only',
        self::IGNORE_ARCHIVED => 'All Leads'
    ];

    public const IS_NOT_SPAM = 0;
    public const IS_SPAM = 1;

    public const IS_FROM_CLASSIFIEDS = 1;
    public const IS_NOT_FROM_CLASSIFIEDS = 0;

    public const IS_BIGTEX_EXPORTED = 1;
    public const IS_BIGTEX_NOT_EXPORTED = 0;

    public const IS_IDS_EXPORTED = 1;
    public const IS_NOT_IDS_EXPORTED = 0;

    public const LEAD_TYPE_CLASSIFIED = 'classified';

    public const TABLE_NAME = 'website_lead';

    /**
     * Lead fields that are related to Customer fields
     * Lead field => Customer field
     */
    public const CUSTOMER_FIELDS = [
        'email_address' => 'email',
        'phone_number' => 'work_phone',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'middle_name' => 'middle_name'
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
    protected $primaryKey = 'identifier';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    public const CREATED_AT = 'date_submitted';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'inventory_id',
        'dealer_id',
        'dealer_location_id',
        'lead_type',
        'referral',
        'title',
        'first_name',
        'last_name',
        'email_address',
        'phone_number',
        'preferred_contact',
        'address',
        'city',
        'state',
        'zip',
        'comments',
        'note',
        'metadata',
        'date_submitted',
        'contact_email_sent',
        'adf_email_sent',
        'cdk_email_sent',
        'newsletter',
        'is_spam',
        'is_archived',
        'is_from_classifieds',
        'bigtex_exported',
        'next_followup',
        'middle_name',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    /**
     * Get the email history for the lead.
     */
    public function emailHistory()
    {
        return $this->hasMany(EmailHistory::class, 'lead_id', 'identifier');
    }

    public function getAllInteractions(): Collection
    {
        $interactionsRepo = app(InteractionsRepositoryInterface::class);
        return $interactionsRepo->getFirst10([
            'include_texts' => true,
            'lead_id' => $this->identifier
        ]);
    }

    /**
     * Get the email history for the lead.
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'tc_lead_id', 'identifier');
    }

    /**
     * Get all products for the lead.
     */
    public function product()
    {
        return $this->hasManyThrough(Product::class, LeadProduct::class, 'lead_id', 'identifier');
    }

    /**
     * Get main inventory for the lead.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    /**
     * Get all units of interest for the lead.
     */
    public function units()
    {
        return $this->belongsToMany(Inventory::class, InventoryLead::class, 'website_lead_id', 'inventory_id', 'identifier');
    }

    public function websiteTracking()
    {
        return $this->hasMany(Tracking::class, 'lead_id', 'identifier');
    }

    /**
     * Get lead types.
     */
    public function leadTypes()
    {
        return $this->hasMany(LeadType::class, 'lead_id', 'identifier');
    }

    /**
     * Get lead types.
     */
    public function textLogs()
    {
        return $this->hasMany(TextLog::class, 'lead_id', 'identifier');
    }

    /**
     * Get lead types.
     */
    public function unitSale()
    {
        return $this->hasMany(UnitSale::class, 'lead_id', 'identifier');
    }

    /**
     * Get Dealer location
     */
    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * Get New Dealer user.
     */
    public function newDealerUser()
    {
        return $this->belongsTo(NewDealerUser::class, 'dealer_id', 'id');
    }

    /**
     * Get Crm User
     *
     * @return HasOneThrough
     */
    public function crmUser(): HasOneThrough
    {
        return $this->hasOneThrough(CrmUser::class, NewDealerUser::class, 'id', 'user_id', 'dealer_id', 'user_id');
    }

    /**
     * Get Website.
     *
     * @return BelongsTo
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');
    }

    /**
     * Get user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
    * @return HasOne
    * @return BelongsToMany
     */
    public function fbLead(): HasOne
    {
        return $this->hasOne(FbLead::class, 'lead_id', 'identifier');
    }

    /**
     * @return BelongsToMany
     */
    public function fbUsers(): BelongsToMany
    {
        return $this->belongsToMany(FbUser::class, FbLead::class, 'lead_id', 'user_id', 'identifier', 'user_id');
    }

    /**
     * Return All Product ID's for Current Lead
     *
     * @return array
     */
    public function getId()
    {
        return $this->processProperty(CompactHelper::expand($this->identifier));
    }

    public function getProductId()
    {
        $productIds = $this->getProductIds();
        return reset($productIds);
    }

    public function getProductIds()
    {
        return $this->product()->pluck('product_id')->toArray();
    }

    /**
     * Retrieves this lead status from the DB
     *
     * @return HasOne
     */
    public function leadStatus(): HasOne
    {
        return $this->hasOne(LeadStatus::class, 'tc_lead_identifier', 'identifier');
    }

    public function getDateSubmitted()
    {
        return $this->processProperty($this->date_submitted);
    }

    /**
     * Retrieves this lead source from the DB
     *
     * @return string
     */
    public function getSource(): string
    {
        if (empty($this->leadStatus)) {
            return '';
        }
        $source = $this->leadStatus()->pluck('source')->toArray();
        return $source['source'] ?? '';
    }

    public function getStatusId()
    {
        return null;
    }

    /**
     * Find Lead Contact Details
     *
     * @param int $id
     * @return array
     */
    public static function findLeadContact(int $id): array
    {
        $result = Lead::findOrFail($id)->pluck('first_name', 'last_name', 'email_address')->toArray();
        return array('name' => $result['first_name'] .' '. $result['last_name'], 'email' => $result['email_address']);
    }


    /**
     * Get Dealer Emails
     *
     * @return array<string>
     */
    public function getDealerEmailsAttribute(): array
    {
        // Get Email From Preferred Location
        if (!empty($this->dealerLocation->email)) {
            return explode(";", $this->dealerLocation->email);
        }

        // Get Email From Unit of Interest Location
        if (!empty($this->inventory->dealerLocation->email)) {
            return explode(";", $this->inventory->dealerLocation->email);
        }

        // Get Email From Dealer
        return explode(";", $this->user->email);
    }

    /**
     * Get Inventory ID's
     *
     * @return array
     */
    public function getInventoryIdsAttribute(): array
    {
        // Initialize Inventory ID's Array
        $inventoryIds = $this->units()->pluck('inventory.inventory_id')->toArray();

        // Append Current Inventory ID
        array_unshift($inventoryIds, $this->inventory_id);

        // Return Full Array
        return $inventoryIds;
    }

    /**
     * Get Inventory Title
     *
     * @return string
     */
    public function getInventoryTitleAttribute(): string
    {
        // Get Inventory Title
        if (!empty($this->inventory) && !empty($this->inventory->title)) {
            return $this->inventory->title;
        }

        // Initialize Inventory Title Array
        $titles = $this->units()->pluck('title')->toArray();
        if (count($titles) > 0) {
            return reset($titles);
        }
        return '';
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the user's full name or ID #.
     *
     * @return string
     */
    public function getIdNameAttribute(): string
    {
        $idName = $this->full_name;
        if (empty($idName)) {
            $idName = "#" . $this->identifier;
        }
        return $idName;
    }

    /**
     * Get the user's text number
     *
     * @return string
     */
    public function getTextPhoneAttribute(): string
    {
        if (empty($this->phone_number)) {
            return '';
        }
        $phone = preg_replace("/[^0-9]/", "", $this->phone_number);
        return '+' . ((strlen($phone) === 11) ? $phone : '1' . substr($phone, 0, 10));
    }

    /**
     * Get cleaned phone for matching
     *
     * @return string
     */
    public function getCleanPhoneAttribute(): string
    {
        if (empty($this->phone_number)) {
            return '';
        }
        $phone = preg_replace("/[-+)( x]+/", "", $this->phone_number);
        return ((strlen($phone) === 11) ? $phone : '1' . $phone);
    }

    /**
     * Get lead types array.
     *
     * @return array
     */
    public function getLeadTypesAttribute(): array
    {
        // Initialize Inventory ID's Array
        $leadTypes = $this->leadTypes()->pluck('lead_type')->toArray();

        // Append Current Lead Type If Not Already in Array
        if (!in_array($this->lead_type, $leadTypes)) {
            array_unshift($leadTypes, $this->lead_type);
        }

        // Return Full Array
        return $leadTypes;
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullAddressAttribute(): ?string
    {
        if (empty($this->address) || empty($this->city) || empty($this->state)) {
            return null;
        }
        return "{$this->address}, {$this->city}, {$this->state}, {$this->zip}";
    }

    /**
     * @return string(phone number) number in format (XXX) NNN-NNNN
     */
    public function getPrettyPhoneNumberAttribute(): ?string
    {
        if (preg_match('/^(\d{3})(\d{3})(\d{4})$/', $this->phone_number, $matches)) {
            return '(' . $matches[1] . ')' . ' ' .$matches[2] . '-' . $matches[3];
        } else {
            return null;
        }
    }

    /**
     * Preferred Dealer Location Attribute
     *
     * @return null|DealerLocation
     */
    public function getPreferredDealerLocationAttribute(): ?DealerLocation
    {
        if (empty($this->preferred_location)) {
            return null;
        }

        return DealerLocation::where('dealer_location_id', $this->preferred_location)->first();
    }

    /**
     * Get Preferred Location Attribute
     *
     * @return int
     */
    public function getPreferredLocationAttribute(): int
    {
        // Dealer Location ID Exists?
        if (!empty($this->dealer_location_id)) {
            return $this->dealer_location_id;
        }

        // Return Inventory Location ID Instead
        if (!empty($this->inventory->dealer_location_id)) {
            return $this->inventory->dealer_location_id;
        }

        // Return Nothing
        return 0;
    }

    public function getInquiryTypeAttribute(): string
    {
        switch($this->lead_type) {
            case LeadType::TYPE_INVENTORY:
            case LeadType::TYPE_CRAIGSLIST:
                return LeadType::TYPE_INVENTORY;
            case LeadType::TYPE_SHOWROOM_MODEL:
                return LeadType::TYPE_SHOWROOM;
            default:
                return LeadType::TYPE_GENERAL;
        }
    }

    /**
     * Get Inquiry Name Attribute
     *
     * @return string
     */
    public function getInquiryNameAttribute(): string
    {
        // Dealer Location Name Exists?
        if (!empty($this->dealerLocation->name)) {
            return $this->dealerLocation->name;
        }

        // Inventory Dealer Location Name Exists?
        if (!empty($this->inventory->dealerLocation->name)) {
            return $this->inventory->dealerLocation->name;
        }

        // Return Dealer Name
        return $this->user->name;
    }

    /**
     * Get Inquiry Email Attribute
     *
     * @return string
     */
    public function getInquiryEmailAttribute(): string
    {
        // Dealer Location Email Exists?
        if (!empty($this->dealerLocation->email)) {
            return $this->dealerLocation->email;
        }

        // Inventory Dealer Location Email Exists?
        if (!empty($this->inventory->dealerLocation->email)) {
            return $this->inventory->dealerLocation->email;
        }

        // Return Dealer Email
        return $this->user->email;
    }


    /**
     * Process the property value to comply with what the interface methods expect
     *
     * @param mixed $property
     * @return mixed
     */
    private function processProperty($property)
    {
        return empty($property) ? null : $property;
    }

    /**
     * Get Purchases for Lead
     *
     * @return array of result data
     */
    public function getInvoices(): array
    {
        // Get Purchases
        if (empty($this->invoices)) {
            $resultSet = $this->unitSale()->pluck('total_price', 'id')->with(function ($query) {
                $query->invoices()->payment()->groupBy('invoice_id')->map(function ($row) {
                    return $row->sum('amount');
                });
            });

            // Loop Purchases
            $invoices = array();
            foreach ($resultSet as $result) {
                $invoices[] = $result;
            }
            $this->invoices = $invoices;
        }

        // Return Results
        return $this->invoices;
    }

    /**
     * Get Total Purchases for Lead
     *
     */
    public function getLifetimeSales()
    {
        // Get Sales
        if (empty($this->lifetime_sales)) {
            $resultSet = $this->unitSale()->invoices()->payment()->groupBy('invoice_id')->map(function ($row) {
                return $row->sum('amount');
            });

            // Loop Purchases
            foreach ($resultSet as $result) {
                $this->lifetime_sales = $result->paid_amount;
            }
        }

        // Return Sales
        if ($this->lifetime_sales > 0) {
            return number_format(round($this->lifetime_sales, 2), 2);
        }
        return 0;
    }

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function getLeadCrmUrl($leadId, $credential): string
    {
        return config('app.new_design_crm_url') . 'user/login?e=' . $credential . '&r=' . urlencode(config('app.crm_lead_url') . CompactHelper::expand($leadId));
    }
}
