<?php
namespace App\Models\Inventory;

use App\Helpers\StringHelper;
use App\Models\Integration\LotVantage\DealerInventory;
use App\Models\User\DealerLocation;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\Traits\TableAware;

class Inventory extends Model
{
    use TableAware;

    const TABLE_NAME = 'inventory';

    const COLOR_ATTRIBUTE_ID = 11;

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
        'condition',
        'length',
        'width',
        'height',
        'gvwr',
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

    public function features()
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
}
