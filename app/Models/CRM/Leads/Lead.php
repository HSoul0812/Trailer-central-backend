<?php

namespace App\Models\CRM\Leads;

use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Product\Product;
use App\Models\CRM\Leads\LeadProduct;
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
    
    const LEAD_NOT_ARCHIVED = 0;
    const LEAD_ARCHIVED = 1;
    
    const TABLE_NAME = 'website_lead';
    
    
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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'lead_type',
        'inventory_id',
        'referral',
        'first_name',
        'title',
        'last_name',
        'phone_number'
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
     * Get all inventories for the lead.
     */
    public function inventory()
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
     * Return All Product ID's for Current Lead
     *
     * @return array
     */
    public function getId() {
        return $this->processProperty(CompactHelper::expand($this->identifier));
    }

    public function getProductIds() {
        return $this->product()->pluck('product_id')->toArray();
    }

    public function getInventoryIds() {
        return $this->inventory()->pluck('inventory_id')->toArray();
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
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return "{$this->first_name} {$this->last_name}";
    }

    public static function findLeadContact($id) {
        $result = Lead::findOrFail($id)->pluck('first_name', 'last_name', 'email_address')->toArray();
        return array('name' => $result['first_name'] .' '. $result['last_name'], 'email' => $result['email_address']);
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
     * Process the property value to comply with what the interface methods expect
     *
     * @param mixed $property
     * @return mixed
     */
    private function processProperty($property) {
        return empty($property) ? null : $property;
    }

//    public function loadFromArray($arr) {
//        foreach($arr as $key => $value) {
//            if($key === 'status') {
//                $value = Lead::getStatus((int)$value);
//            } elseif($key === 'lead_type') {
//                if(is_array($value)) {
//                    $value = reset($value);
//                }
//            }
//            $this->$key = $value;
//        }
//    }

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
}