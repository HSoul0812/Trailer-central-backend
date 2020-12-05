<?php
namespace App\Models\Inventory;

use App\Helpers\StringHelper;
use App\Models\Integration\LotVantage\DealerInventory;
use App\Models\User\DealerLocation;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use App\Traits\CompactHelper;
use ElasticScoutDriverPlus\CustomSearch;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use App\Models\Traits\TableAware;
use Laravel\Scout\Searchable;

class Inventory extends Model
{
    use TableAware, SpatialTrait, Searchable, CustomSearch;

    const COLOR_ATTRIBUTE_ID = 11;

    const TABLE_NAME = 'inventory';

    const STATUS_QUOTE = 6;
    const STATUS_AVAILABLE = 1;
    const STATUS_SOLD = 2;
    const STATUS_ON_ORDER = 3;
    const STATUS_PENDING_SALE = 4;
    const STATUS_SPECIAL_ORDER = 5;

    const STATUS_QUOTE_LABEL = 'Quote';
    const STATUS_AVAILABLE_LABEL = 'Available';
    const STATUS_SOLD_LABEL = 'Sold';
    const STATUS_ON_ORDER_LABEL = 'On Order';
    const STATUS_PENDING_SALE_LABEL = 'Pending Sale';
    const STATUS_SPECIAL_ORDER_LABEL = 'Special Order';

    const STATUS_MAPPING = [
        self::STATUS_QUOTE          => self::STATUS_QUOTE_LABEL,
        self::STATUS_AVAILABLE      => self::STATUS_AVAILABLE_LABEL,
        self::STATUS_SOLD           => self::STATUS_SOLD_LABEL,
        self::STATUS_ON_ORDER       => self::STATUS_ON_ORDER_LABEL,
        self::STATUS_PENDING_SALE   => self::STATUS_PENDING_SALE_LABEL,
        self::STATUS_SPECIAL_ORDER  => self::STATUS_SPECIAL_ORDER_LABEL
    ];

    const CONDITION_NEW = 'new';
    const CONDITION_USED = 'used';
    const CONDITION_RE_MFG = 'remfg';

    const CONDITION_MAPPING = [
        self::CONDITION_NEW => 'New',
        self::CONDITION_USED => 'Used',
        self::CONDITION_RE_MFG => 'Re-manufactured',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'inventory_id';

    protected $fillable = [
        'fp_balance',
        'fp_interest_paid',
        'length',
        'width',
        'height'
    ];

    protected $casts = [
        'is_archived' => 'integer',
        'length' => 'float',
        'length_inches' => 'float',
        'width' => 'float',
        'width_inches' => 'float',
        'height' => 'float',
        'height_inches' => 'float',
        'weight' => 'float',
        'true_cost' => 'float',
        'price' => 'float',
        'msrp' => 'float',
        'gvwr' => 'float'
    ];

    protected $hidden = [
        'geolocation'
    ];

    protected $spatialFields = [
        'geolocation'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'inventory_id', 'inventory_id', InventoryLead::class);
    }

    public function attribute()
    {
        return $this->hasManyThrough(Attribute::class, 'eav_attribute_value', 'attribute_id', 'inventory_id');
    }

    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function floorplanPayments()
    {
        return $this->hasMany('App\Models\Inventory\Floorplan\Payment', 'inventory_id', 'inventory_id');
    }

    public function images()
    {
        return $this->hasManyThrough(Image::class, InventoryImage::class, 'inventory_id', 'image_id', 'inventory_id', 'image_id');
    }

    public function files()
    {
        return $this->hasManyThrough(File::class, InventoryFile::class, 'inventory_id', 'id', 'inventory_id', 'file_id');
    }

    public function inventoryFeatures()
    {
        return $this->hasMany(InventoryFeature::class, 'inventory_id', 'inventory_id');
    }

    public function clapps()
    {
        return $this->hasMany(InventoryClapp::class, 'inventory_id', 'inventory_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class, 'inventory_id', 'inventory_id');
    }

    public function lotVantageInventory()
    {
        return $this->hasOne(DealerInventory::class, 'inventory_id', 'inventory_id');
    }


    public function status()
    {
        return $this->belongsTo(Status::class, 'status');
    }

    public function floorplanVendor()
    {
        return $this->belongsTo(Vendor::class, 'fp_vendor');
    }

    public function getColorAttribute()
    {
        $color = self::select('*')
                    ->join('eav_attribute_value', 'inventory.inventory_id', '=', 'eav_attribute_value.inventory_id')
                    ->where('inventory.inventory_id', $this->inventory_id)
                    ->where('eav_attribute_value.attribute_id', self::COLOR_ATTRIBUTE_ID)
                    ->first();
        if ($color) {
            return $color->value;
        }

        return null;
    }

    public function getStatusLabelAttribute()
    {
        return isset(self::STATUS_MAPPING[$this->status]) ? self::STATUS_MAPPING[$this->status] : null;
    }

    public function __toString() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        $url = '/';
        $url .= StringHelper::superSanitize($this->title, '-');
        $url .= '-' . CompactHelper::shorten($this->inventory_id);

        $url .= '.html';

        return $url;
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }

    public function searchableAs()
    {
        return env('INDEX_INVENTORY', 'inventory');
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();
        return $array;
    }
}
