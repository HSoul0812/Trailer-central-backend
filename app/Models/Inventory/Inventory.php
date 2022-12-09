<?php
namespace App\Models\Inventory;

use App\Helpers\SanitizeHelper;
use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\CRM\Dms\ServiceOrder;
use App\Models\Integration\LotVantage\DealerInventory;
use App\Models\Inventory\Floorplan\Payment;
use App\Models\Marketing\Facebook\Listings;
use App\Models\User\DealerLocation;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use App\Traits\CompactHelper;
use App\Traits\GeospatialHelper;
use ElasticScoutDriverPlus\CustomSearch;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Models\Inventory\AttributeValue;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parts\Vendor;
use App\Models\User\User;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Laravel\Scout\Searchable;

/**
 * Class Inventory
 * @package App\Models\Inventory
 *
 * @property int $inventory_id,
 * @property int $entity_type_id,
 * @property int $dealer_id,
 * @property int $dealer_location_id,
 * @property bool $active,
 * @property string $title,
 * @property string $stock,
 * @property string $manufacturer,
 * @property string $brand,
 * @property string $model,
 * @property int $qb_item_category_id,
 * @property string $description,
 * @property string $description_html,
 * @property int $status,
 * @property string $availability,
 * @property bool $is_consignment,
 * @property string $category,
 * @property string $video_embed_code,
 * @property string $vin,
 * @property array $geolocation,
 * @property double $msrp_min,
 * @property double $msrp,
 * @property double $price,
 * @property double $sales_price,
 * @property double $use_website_price,
 * @property double $website_price,
 * @property double $dealer_price,
 * @property double $monthly_payment,
 * @property int $year,
 * @property int $chassis_year,
 * @property string $condition,
 * @property double $length,
 * @property double $width,
 * @property double $height,
 * @property double $gvwr,
 * @property double $weight,
 * @property double $axle_capacity,
 * @property string $cost_of_unit,
 * @property double $true_cost,
 * @property string $cost_of_shipping,
 * @property string $cost_of_prep,
 * @property string $total_of_cost,
 * @property double $pac_amount,
 * @property string $pac_type,
 * @property double $minimum_selling_price,
 * @property string $notes,
 * @property bool $show_on_ksl,
 * @property bool $show_on_racingjunk,
 * @property bool $show_on_website,
 * @property bool $overlay_enabled,
 * @property bool $is_special,
 * @property bool $is_featured,
 * @property double $latitude,
 * @property double $longitude,
 * @property \DateTimeInterface $archived_at,
 * @property bool $broken_video_embed_code,
 * @property int $showroom_id,
 * @property int $coordinates_updated,
 * @property double $payload_capacity,
 * @property string $height_display_mode,
 * @property string $width_display_mode,
 * @property string $length_display_mode,
 * @property double $width_inches,
 * @property double $height_inches,
 * @property double $length_inches,
 * @property bool $show_on_rvtrader,
 * @property string $chosen_overlay,
 * @property \DateTimeInterface $fp_committed,
 * @property int $fp_vendor,
 * @property double $fp_balance,
 * @property bool $fp_paid,
 * @property double $fp_interest_paid, PRTBND-985 We won't use this field anymore. Will remove it soon.
 * @property string $l_holder,
 * @property string $l_attn,
 * @property string $l_name_on_account,
 * @property string $l_address,
 * @property string $l_account,
 * @property string $l_city,
 * @property string $l_state,
 * @property string $l_zip_code,
 * @property double $l_payoff,
 * @property string $l_phone,
 * @property bool $l_paid,
 * @property string $l_fax,
 * @property string $bill_id,
 * @property bool $send_to_quickbooks,
 * @property bool $is_floorplan_bill,
 * @property string $integration_item_hash,
 * @property string $integration_images_hash,
 * @property bool $non_serialized,
 * @property double $hidden_price,
 * @property \DateTimeInterface $utc_integration_updated_at,
 * @property bool $has_stock_images,
 * @property bool $qb_sync_processed,
 * @property array|null $changed_fields_in_dashboard
 * @property string $identifier
 * @property int $times_viewed
 * @property bool $is_archived
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property bool $show_on_auction123
 * @property bool $show_on_rvt
 *
 * @property string $category_label
 * @property string $status_label
 * @property string $color
 * @property double $interest_paid
 * @property double $cost_of_ros
 *
 * @property User $user
 * @property Lead $lead
 * @property Collection<Attribute> $attribute
 * @property DealerLocation $dealerLocation
 * @property Collection<Payment> $floorplanPayments
 * @property Collection<InventoryImage> $inventoryImages
 * @property Collection<Image> $images
 * @property Collection<InventoryFile> $inventoryFiles
 * @property Collection<File> $files
 * @property Collection<InventoryFeature> $inventoryFeatures
 * @property Collection<InventoryClapp> $clapps
 * @property Collection<ServiceOrder> $repairOrders
 * @property Collection<AttributeValue> $attributeValues
 * @property Collection<CustomerInventory> $customerInventory
 * @property DealerInventory $lotVantageInventory
 * @property Vendor $floorplanVendor
 *
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Inventory extends Model
{
    use TableAware, SpatialTrait, GeospatialHelper, Searchable, CustomSearch;

    const CONSTRUCTION_ATTRIBUTE_ID = 2;
    const COLOR_ATTRIBUTE_ID = 11;
    const FUEL_TYPE_ATTRIBUTE_ID = 14;
    const MILEAGE_ATTRIBUTE_ID = 16;

    const TABLE_NAME = 'inventory';

    const STATUS_QUOTE = 6;
    const STATUS_AVAILABLE = 1;
    const STATUS_SOLD = 2;
    const STATUS_ON_ORDER = 3;
    const STATUS_PENDING_SALE = 4;
    const STATUS_SPECIAL_ORDER = 5;
    const STATUS_NULL = null;

    const STATUS_QUOTE_LABEL = 'Quote';
    const STATUS_AVAILABLE_LABEL = 'Available';
    const STATUS_SOLD_LABEL = 'Sold';
    const STATUS_ON_ORDER_LABEL = 'On Order';
    const STATUS_PENDING_SALE_LABEL = 'Pending Sale';
    const STATUS_SPECIAL_ORDER_LABEL = 'Special Order';

    const UNAVAILABLE_STATUSES = [
        self::STATUS_SOLD,
        self::STATUS_ON_ORDER,
        self::STATUS_PENDING_SALE,
        self::STATUS_SPECIAL_ORDER,
        self::STATUS_QUOTE
    ];

    const IS_FLOORPLANNED = 1;
    const IS_NOT_FLOORPLANNED = 0;

    const IS_ARCHIVED = 1;
    const IS_NOT_ARCHIVED = 0;

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

    const OVERLAY_ENABLED_PRIMARY = 1;
    const OVERLAY_ENABLED_ALL = 2;

    const OVERLAY_CODES = [
        self::OVERLAY_ENABLED_PRIMARY,
        self::OVERLAY_ENABLED_ALL,
    ];

    public const MIN_DESCRIPTION_LENGTH_FOR_FACEBOOK = 50;
    public const MIN_PRICE_FOR_FACEBOOK = 0;

    const PAC_TYPE_PERCENT = 'percent';
    const PAC_TYPE_AMOUNT = 'amount';

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
        'entity_type_id',
        'dealer_id',
        'dealer_location_id',
        'active',
        'title',
        'stock',
        'manufacturer',
        'brand',
        'model',
        'qb_item_category_id',
        'description',
        'description_html',
        'status',
        'availability',
        'is_consignment',
        'category',
        'video_embed_code',
        'vin',
        'geolocation',
        'msrp_min',
        'msrp',
        'price',
        'sales_price',
        'use_website_price',
        'website_price',
        'dealer_price',
        'monthly_payment',
        'year',
        'chassis_year',
        'condition',
        'length',
        'width',
        'height',
        'gvwr',
        'weight',
        'axle_capacity',
        'cost_of_unit',
        'true_cost',
        'cost_of_shipping',
        'cost_of_prep',
        'total_of_cost',
        'pac_amount',
        'pac_type',
        'minimum_selling_price',
        'notes',
        'show_on_ksl',
        'show_on_racingjunk',
        'show_on_website',
        'overlay_enabled',
        'is_special',
        'is_featured',
        'latitude',
        'longitude',
        'archived_at',
        'broken_video_embed_code',
        'showroom_id',
        'coordinates_updated',
        'payload_capacity',
        'height_display_mode',
        'width_display_mode',
        'length_display_mode',
        'width_inches',
        'height_inches',
        'length_inches',
        'show_on_rvtrader',
        'chosen_overlay',
        'fp_committed',
        'fp_vendor',
        'fp_balance',
        'fp_paid',
        'fp_interest_paid',
        'l_holder',
        'l_attn',
        'l_name_on_account',
        'l_address',
        'l_account',
        'l_city',
        'l_state',
        'l_zip_code',
        'l_payoff',
        'l_phone',
        'l_paid',
        'l_fax',
        'bill_id',
        'send_to_quickbooks',
        'is_floorplan_bill',
        'integration_item_hash',
        'integration_images_hash',
        'non_serialized',
        'hidden_price',
        'utc_integration_updated_at',
        'has_stock_images',
        'qb_sync_processed',
        'changed_fields_in_dashboard',
        'is_archived',
        'times_viewed',
        'trailerworld_store_id',
        'show_on_auction123',
        'show_on_rvt'
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
        'gvwr' => 'float',
        'fp_balance' => 'float',
        'qb_sync_processed' => 'boolean',
        'is_floorplan_bill' => 'boolean',
        'sold_at' => 'datetime',
        'changed_fields_in_dashboard' => 'array'
    ];

    protected $hidden = [
        'geolocation'
    ];

    protected $spatialFields = [
        'geolocation'
    ];


    /**
     * Custom Attributes Collection
     *
     * @var Collection
     */
    private $attributesCollection;

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootSearchable()
    {
        // We don't want to do anything with searchable for this model
        // If we remove Searchable, it will remove parts index as well
        // from ES, so for now we'll just rewrite it to nothing
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'inventory_id', 'inventory_id', InventoryLead::class);
    }

    public function attribute(): HasManyThrough
    {
        return $this->hasManyThrough(Attribute::class, AttributeValue::class, 'eav_attribute_value', 'attribute_id', 'inventory_id');
    }

    public function dealerLocation(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id')->withTrashed();
    }

    public function floorplanPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'inventory_id', 'inventory_id');
    }

    public function inventoryImages(): HasMany
    {
        return $this->hasMany(InventoryImage::class, 'inventory_id', 'inventory_id');
    }

    public function orderedImages(): HasMany
    {
        return $this->inventoryImages()->has('image')->with('image')
                    ->orderByRaw('IFNULL(position, 99) ASC')
                    ->orderBy('image_id', 'ASC');
    }

    public function images(): HasManyThrough
    {
        return $this->hasManyThrough(Image::class, InventoryImage::class, 'inventory_id', 'image_id', 'inventory_id', 'image_id');
    }

    public function inventoryFiles(): HasMany
    {
        return $this->hasMany(InventoryFile::class, 'inventory_id', 'inventory_id');
    }

    public function repairOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'inventory_id', 'inventory_id');
    }

    public function files(): HasManyThrough
    {
        return $this->hasManyThrough(File::class, InventoryFile::class, 'inventory_id', 'id', 'inventory_id', 'file_id');
    }

    public function inventoryFeatures(): HasMany
    {
        return $this->hasMany(InventoryFeature::class, 'inventory_id', 'inventory_id');
    }

    public function clapps(): HasMany
    {
        return $this->hasMany(InventoryClapp::class, 'inventory_id', 'inventory_id');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'inventory_id', 'inventory_id');
    }

    public function lotVantageInventory(): HasOne
    {
        return $this->hasOne(DealerInventory::class, 'inventory_id', 'inventory_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status');
    }

    public function floorplanVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'fp_vendor');
    }

    public function customerInventories(): HasMany
    {
        return $this->hasMany(CustomerInventory::class, 'inventory_id', 'inventory_id');
    }

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class,'entity_type_id');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class, 'id', 'bill_id');
    }

    /**
     * Get Attributes Map
     *
     * @return Collection<code: value>
     */
    public function getAttributesAttribute(): Collection
    {
        // Attributes Already Exist?
        if(empty($this->attributesCollection)) {
            // Initialize Attributes
            $attributes = [];

            // Loop Attributes
            foreach($this->attributeValues as $value) {
                $attributes[$value->attribute->code] = $value->value;
            }

            // Set Attributes Collection
            $this->attributesCollection = new Collection($attributes);
        }

        // Return Attribute Map
        return $this->attributesCollection;
    }

    public function getPrimaryImageAttribute(): ?InventoryImage
    {
        return $this->orderedImages()->first();
    }

    public function getCategoryLabelAttribute()
    {
        $category = Category::where('legacy_category', $this->category)->first();

        if (empty($category)) {
            return null;
        }

        return $category->label;
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

    /**
     * Get Construction
     *
     * @return string
     */
    public function getConstructionAttribute(): ?string
    {
        // Get Construction
        $attribute = $this->attributeValues()->where('attribute_id', self::CONSTRUCTION_ATTRIBUTE_ID)->first();

        // Return Value
        return $attribute->value ?? '';
    }

    public function getIdentifierAttribute(): string
    {
        return CompactHelper::shorten($this->inventory_id);
    }

    /**
     * Get Fuel Type
     *
     * @return string
     */
    public function getFuelTypeAttribute(): ?string
    {
        // Get Fuel Type
        $attribute = $this->attributeValues()->where('attribute_id', self::FUEL_TYPE_ATTRIBUTE_ID)->first();

        // Return Value
        return $attribute->value ?? '';
    }

    /**
     * Get Mileage
     *
     * @return string
     */
    public function getMileageAttribute(): ?string
    {
        // Get Attribute
        $attribute = $this->attributeValues()->where('attribute_id', self::MILEAGE_ATTRIBUTE_ID)->first();

        // Return Value
        return $attribute->value ?? '';
    }

    /**
     * @return float|null
     */
    public function getCostOfRosAttribute(): ?float
    {
        return $this->repairOrders()->sum('total_price');
    }

    /**
     * @return string|null
     */
    public function getStatusLabelAttribute(): ?string
    {
        return self::STATUS_MAPPING[$this->status] ?? null;
    }

    /**
     * Instead of using fp_interest_paid field from inventory table, calculate this amount from payment history table
     *
     * @return float An amount of interest paid
     */
    public function getInterestPaidAttribute(): float
    {
        $interest_paid = self::select('SUM(inventory_floor_plan_payment.amount) AS interest_paid')
                    ->join('inventory_floor_plan_payment', 'inventory.inventory_id', '=', 'inventory_floor_plan_payment.inventory_id')
                    ->where('inventory.inventory_id', $this->inventory_id)
                    ->where('inventory_floor_plan_payment.type', Payment::PAYMENT_CATEGORIES['Interest'])
                    ->sum('inventory_floor_plan_payment.amount');

        return $interest_paid;
    }

    public function __toString() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        $sanitizeHelper = new SanitizeHelper();

        $url = '/';
        $url .= $sanitizeHelper->superSanitize($this->title, '-');
        $url .= '-' . CompactHelper::shorten($this->inventory_id);

        $url .= '.html';

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function save(array $options = [])
    {
        if (!empty($this->geolocation) && is_string($this->geolocation)) {
            $geometry = $this->fromWKB($this->geolocation);
            $this->geolocation = new Point($geometry['lat'], $geometry['lon']);
        }

        return parent::save($options);
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

    public function listings(): HasMany
    {
        return $this->hasMany(Listings::class, 'inventory_id', 'inventory_id');
    }

    public function activeListings()
    {
        return $this->listings()->whereNotIn('status', ['expired', 'deleted']);
    }
}
