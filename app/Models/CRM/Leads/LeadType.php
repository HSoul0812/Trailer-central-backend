<?php


namespace App\Models\CRM\Leads;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TableAware;

class LeadType extends Model
{
    use TableAware;

    const TYPE_GENERAL = 'general';
    const TYPE_MANUAL = 'manual';
    const TYPE_CRAIGSLIST = 'craigslist';
    const TYPE_INVENTORY = 'inventory';
    const TYPE_TEXT = 'text';
    const TYPE_SHOWROOM = 'showroom';
    const TYPE_SHOWROOM_MODEL = 'showroomModel';
    const TYPE_JOTFORM = 'jotform';
    const TYPE_BUILD = 'build';
    const TYPE_RENTALS = 'rentals';
    const TYPE_FINANCING = 'financing';
    const TYPE_SERVICE = 'service';
    const TYPE_CALL = 'call';
    const TYPE_TRADE = 'trade';
    const TYPE_FB = 'facebook';
    const TYPE_NONLEAD = 'nonlead';

    const TYPE_ARRAY = [
        self::TYPE_GENERAL,
        self::TYPE_CRAIGSLIST,
        self::TYPE_INVENTORY,
        self::TYPE_TEXT,
        self::TYPE_SHOWROOM_MODEL,
        self::TYPE_BUILD,
        self::TYPE_RENTALS,
        self::TYPE_FINANCING,
        self::TYPE_SERVICE,
        self::TYPE_CALL,
        self::TYPE_TRADE
    ];

    const TYPE_ARRAY_FULL = [
        self::TYPE_GENERAL,
        self::TYPE_MANUAL,
        self::TYPE_CRAIGSLIST,
        self::TYPE_INVENTORY,
        self::TYPE_TEXT,
        self::TYPE_SHOWROOM_MODEL,
        self::TYPE_JOTFORM,
        self::TYPE_BUILD,
        self::TYPE_RENTALS,
        self::TYPE_FINANCING,
        self::TYPE_SERVICE,
        self::TYPE_CALL,
        self::TYPE_TRADE,
        self::TYPE_FB,
        self::TYPE_NONLEAD
    ];

    const PUBLIC_TYPES = [
        self::TYPE_GENERAL => 'General',
        self::TYPE_INVENTORY => 'Inventory',
        self::TYPE_FINANCING => 'Financing',
        self::TYPE_RENTALS => 'Rentals',
        self::TYPE_TRADE => 'Trade In',
        self::TYPE_BUILD => 'Build a Trailer',
        self::TYPE_SERVICE => 'Service',
        self::TYPE_CRAIGSLIST => 'Craigslist',
        self::TYPE_CALL => 'Click to Call',
    ];

    const TABLE_NAME = 'website_lead_types';

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
    protected $primaryKey = 'lead_type_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'lead_type'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'added';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

    /**
     * Get lead.
     */
    public function lead()
    {
        return $this->hasOne(LeadType::class, 'identifier', 'lead_id');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
