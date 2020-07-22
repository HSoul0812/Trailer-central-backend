<?php

namespace App\Models\CRM\Leads;

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Product\Product;
use App\Models\CRM\Leads\LeadProduct;
use App\Models\User\DealerLocation;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\Inventory\Inventory;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\Traits\TableAware;

class Lead extends Model
{
    use TableAware;
    
    const STATUS_WON = 'Closed';
    const STATUS_WON_CLOSED = 'Closed (Won)';    
    const STATUS_LOST = 'Closed (Lost)';
    const STATUS_HOT = 'Hot';
    const STATUS_COLD = 'Cold';
    const STATUS_MEDIUM = 'Medium';
    const STATUS_UNCONTACTED = 'Uncontacted';
    const STATUS_NEW_INQUIRY = 'New Inquiry';
    
    const NOT_ARCHIVED = 0; 
    const LEAD_ARCHIVED = 1;
    
    const TABLE_NAME = 'website_lead';

    const STATES_LIST = [
        'AL' => 'ALABAMA',
        'AK' => 'ALASKA',
        'AS' => 'AMERICAN SAMOA',
        'AZ' => 'ARIZONA',
        'AR' => 'ARKANSAS',
        'CA' => 'CALIFORNIA',
        'CO' => 'COLORADO',
        'CT' => 'CONNECTICUT',
        'DE' => 'DELAWARE',
        'DC' => 'DISTRICT OF COLUMBIA',
        'FM' => 'FEDERATED STATES OF MICRONESIA',
        'FL' => 'FLORIDA',
        'GA' => 'GEORGIA',
        'GU' => 'GUAM GU',
        'HI' => 'HAWAII',
        'ID' => 'IDAHO',
        'IL' => 'ILLINOIS',
        'IN' => 'INDIANA',
        'IA' => 'IOWA',
        'KS' => 'KANSAS',
        'KY' => 'KENTUCKY',
        'LA' => 'LOUISIANA',
        'ME' => 'MAINE',
        'MH' => 'MARSHALL ISLANDS',
        'MD' => 'MARYLAND',
        'MA' => 'MASSACHUSETTS',
        'MI' => 'MICHIGAN',
        'MN' => 'MINNESOTA',
        'MS' => 'MISSISSIPPI',
        'MO' => 'MISSOURI',
        'MT' => 'MONTANA',
        'NE' => 'NEBRASKA',
        'NV' => 'NEVADA',
        'NH' => 'NEW HAMPSHIRE',
        'NJ' => 'NEW JERSEY',
        'NM' => 'NEW MEXICO',
        'NY' => 'NEW YORK',
        'NC' => 'NORTH CAROLINA',
        'ND' => 'NORTH DAKOTA',
        'MP' => 'NORTHERN MARIANA ISLANDS',
        'OH' => 'OHIO',
        'OK' => 'OKLAHOMA',
        'OR' => 'OREGON',
        'PW' => 'PALAU',
        'PA' => 'PENNSYLVANIA',
        'PR' => 'PUERTO RICO',
        'RI' => 'RHODE ISLAND',
        'SC' => 'SOUTH CAROLINA',
        'SD' => 'SOUTH DAKOTA',
        'TN' => 'TENNESSEE',
        'TX' => 'TEXAS',
        'UT' => 'UTAH',
        'VT' => 'VERMONT',
        'VI' => 'VIRGIN ISLANDS',
        'VA' => 'VIRGINIA',
        'WA' => 'WASHINGTON',
        'WV' => 'WEST VIRGINIA',
        'WI' => 'WISCONSIN',
        'WY' => 'WYOMING',
        'AE' => 'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
        'AA' => 'ARMED FORCES AMERICA (EXCEPT CANADA)',
        'AP' => 'ARMED FORCES PACIFIC'
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
    const CREATED_AT = 'date_submitted';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'dealer_id',
        'dealer_location_id',
        'lead_type',
        'inventory_id',
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
        'contact_email_sent',
        'adf_email_sent',
        'cdk_email_sent',
        'newsletter',
        'is_spam',
        'is_archived'
    ];

    /**
     * Get the email history for the lead.
     */
    public function emailHistory()
    {
        return $this->hasMany(EmailHistory::class, 'lead_id', 'identifier');
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
        return $this->belongsToMany(Inventory::class, InventoryLead::class, 'website_lead_id', 'inventory_id');
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
     * Return All Product ID's for Current Lead
     *
     * @return array
     */
    public function getId() {
        return $this->processProperty(CompactHelper::expand($this->identifier));
    }

    public function getProductId() {
        $productIds = $this->getProductIds();
        return reset($productIds);
    }

    public function getProductIds() {
        return $this->product()->pluck('product_id')->toArray();
    }

    /**
     * Retrieves this lead status from the DB
     *
     * @return string
     */
    public function leadStatus() {
        return $this->hasOne(LeadStatus::class, 'tc_lead_identifier', 'identifier');
    }

    public function getDateSubmitted() {
        return $this->processProperty($this->date_submitted);
    }

    /**
     * Retrieves this lead source from the DB
     *
     * @return string
     */
    public function getSource() {
        $source = $this->status()->pluck('source')->toArray();
        return $source['status'];
    }

    public function getStatusId() {
        return null;
    }

    /**
     * Find Lead Contact Details
     * 
     * @param type $id
     * @return type
     */
    public static function findLeadContact($id) {
        $result = Lead::findOrFail($id)->pluck('first_name', 'last_name', 'email_address')->toArray();
        return array('name' => $result['first_name'] .' '. $result['last_name'], 'email' => $result['email_address']);
    }


    /**
     * Get Inventory ID's
     * 
     * @return array
     */
    public function getInventoryIdsAttribute() {
        // Initialize Inventory ID's Array
        $inventoryIds = $this->units()->pluck('inventory_id')->toArray();

        // Append Current Inventory ID
        array_unshift($inventoryIds, $this->inventory_id);

        // Return Full Array
        return $inventoryIds;
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the user's full name or ID #.
     *
     * @return string
     */
    public function getIdNameAttribute() {
        $idName = $this->getFullNameAttribute();
        if(empty($idName)) {
            $idName = "#" . $this->identifier;
        }
        return $idName;
    }

    /**
     * Get the user's text number
     * 
     * @return string
     */
    public function getTextPhoneAttribute() {
        return '+' . ((strlen($this->phone_number) === 11) ? $this->phone_number : '1' . $this->phone_number);
    }

    /**
     * Get lead types array.
     *
     * @return array
     */
    public function getLeadTypesAttribute() {
        // Initialize Inventory ID's Array
        $leadTypes = $this->leadTypes()->pluck('lead_type')->toArray();

        // Append Current Lead Type If Not Already in Array
        if(!in_array($this->lead_type, $leadTypes)) {
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
    public function getFullAddressAttribute() {
        if(empty($this->address) || empty($this->city) || empty($this->state)) {
            return null;
        }
        return "{$this->address}, {$this->city}, {$this->state}, {$this->zip}";
    }

    /**
     * @return string(phone number) number in format (XXX) NNN-NNNN
     */
    public function getPrettyPhoneNumberAttribute() {        
        if(  preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $this->phone_number,  $matches ) ) {
            return '(' . $matches[1] . ')' . ' ' .$matches[2] . '-' . $matches[3];
        } else {
            return null;
        }
    }

    /**
     * Get Preferred Location Attribute
     * 
     * @return int
     */
    public function getPreferredLocationAttribute() {
        // Dealer Location ID Exists?
        if(!empty($this->dealer_location_id)){
            return $this->dealer_location_id;
        }

        // Return Inventory Location ID Instead
        if(!empty($this->inventory->dealer_location_id)) {
            return $this->inventory->dealer_location_id;
        }

        // Return Nothing
        return 0;
    }

    /**
     * Process the property value to comply with what the interface methods expect
     *
     * @param mixed $property
     * @return mixed
     */
    private function processProperty($property) {
        return empty($property) ? null : $property;
    }

    /**
     * Get Purchases for Lead
     *
     * @param int $leadId
     * @return array of result data
     * @throws \Exception
     */
    public function getInvoices() {
        // Get Purchases
        if(empty($this->invoices)) {
            $resultSet = $this->unitSale()->pluck('total_price', 'id')->with(function ($query) {
                $query->invoices()->payment()->groupBy('invoice_id')->map(function ($row) {
                    return $row->sum('amount');
                });
            });

            // Loop Purchases
            $invoices = array();
            foreach($resultSet as $result) {
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
     * @param int $leadId
     * @return string  of result data
     */
    public function getLifetimeSales() {
        // Get Sales
        if(empty($this->lifetime_sales)) {
            $resultSet = $this->unitSale()->invoices()->payment()->groupBy('invoice_id')->map(function ($row) {
                return $row->sum('amount');
            });

            // Loop Purchases
            foreach($resultSet as $result) {
                $this->lifetime_sales = $result->paid_amount;
            }
        }

        // Return Sales
        if($this->lifetime_sales > 0) {
            return number_format(round($this->lifetime_sales, 2), 2);
        }
        return 0;
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }

    public static function getLeadCrmUrl($leadId, $credential) {
        return env('CRM_LOGIN_URL') . $credential . '&r=' . urlencode(env('CRM_LEAD_ROUTE') . CompactHelper::expand($leadId));
    }
}
