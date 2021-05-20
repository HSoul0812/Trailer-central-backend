<?php

namespace App\Models\User;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use App\Models\CRM\Text\Number;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DealerLocation
 * @package App\Models\User
 *
 * @property int $dealer_location_id
 * @property int $dealer_id
 * @property string $name
 * @property string $contact
 * @property string $website
 * @property string $email
 * @property string $address
 * @property string $city
 * @property string $county
 * @property string $region
 * @property string $country
 * @property string $postalcode
 * @property string $fax
 * @property string $phone
 * @property int $is_default
 * @property int $sms
 * @property string $sms_phone
 * @property int $permanent_phone
 * @property int $show_on_website_locations
 * @property array $sales_tax_item_column_titles
 * @property string $county_issued
 * @property string $state_issued
 * @property string $dealer_license_no
 * @property string $federal_id
 * @property float $pac_amount
 * @property string $pac_type
 * @property int $coordinates_updated
 * @property int $is_default_for_invoice
 * @property float $latitude
 * @property float $longitude
 * @property string $location_id
 *
 * @property-read DealerLocationSalesTax salesTax
 *
 * @method static \Illuminate\Database\Query\Builder select($columns = ['*'])
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static DealerLocation findOrFail($id, array $columns = ['*'])
 * @method static DealerLocation|Collection|static[]|static|null find($id, $columns = ['*'])
 */
class DealerLocation extends Model
{
    use TableAware;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_location';

    protected $guarded = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'dealer_location_id';

    const DEFAULT_SALES_TAX_ITEM_COLUMN_TITLES = [
         'standard' => 'Standard',
         'tax_exempt' => 'Tax Exempt',
         'out_of_state_reciprocal' => 'Out-of-state Reciprocal',
         'out_of_state_non_reciprocal' => 'Out-of-state Non-Reciprocal'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_id",
        "is_default",
        "is_default_for_invoice",
        "name",
        "contact",
        "website",
        "phone",
        "fax",
        "email",
        "address",
        "city",
        "county",
        "region",
        "postalcode",
        "country",
        "geolocation",
        "latitude",
        "longitude",
        "coordinates_updated",
        "sms",
        "sms_phone",
        "permanent_phone",
        "show_on_website_locations",
        "county_issued",
        "state_issued",
        "dealer_license_no",
        "federal_id",
        "pac_amount",
        "pac_type",
        'sales_tax_item_column_titles',
        'location_id'
    ];

    protected $casts = [
        'sales_tax_item_column_titles' => 'array'
    ];

    /**
     * @return type
     */
    public function dealer()
    {
        return $this->belongsTo(NewDealerUser::class, 'dealer_id', 'id');
    }

    /**
     * @return type
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function inventoryCount(): int
    {
        return $this->dealer_location_id ?
            Inventory::where('dealer_location_id', $this->dealer_location_id)->count() : 0;
    }

    public function referenceCount(): int
    {
        return $this->dealer_location_id ?
            ApiEntityReference::where([
                'entity_id' => $this->dealer_location_id,
                'entity_type' => ApiEntityReference::TYPE_LOCATION
            ])->count() : 0;
    }

    public function salesTax():HasOne
    {
        return $this->hasOne(DealerLocationSalesTax::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function salesTaxItems(): HasMany
    {
        return $this->HasMany(DealerLocationSalesTaxItem::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function salesTaxItemsV1(): HasMany
    {
        return $this->HasMany(DealerLocationSalesTaxItemV1::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * @return type
     */
    public function number()
    {
        return $this->belongsTo(Number::class, 'sms_phone', 'dealer_number');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(DealerLocationQuoteFee::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function hasRelatedRecords(): bool
    {
        if (empty($this->dealer_location_id)) {
            return false;
        }

        $numberOfInventories = Inventory::where('dealer_location_id', $this->dealer_location_id)->count();
        $numberOfReferences = ApiEntityReference::where([
            'entity_id' => $this->dealer_location_id,
            'entity_type' => ApiEntityReference::TYPE_LOCATION
        ])->count();

        return $numberOfInventories || $numberOfReferences;
    }
}
