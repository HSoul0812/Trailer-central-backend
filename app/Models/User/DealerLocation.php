<?php

namespace App\Models\User;

use App\Helpers\GeographyHelper;
use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\Inventory\Inventory;
use App\Models\Region;
use App\Observers\User\DealerLocationObserver;
use App\Models\Traits\TableAware;
use App\Models\CRM\Text\Number;
use App\Models\User\Location\QboLocationMapping;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * Class DealerLocation
 * @package App\Models\User
 *
 * @property int $dealer_location_id
 * @property int $dealer_id
 * @property string $name
 * @property string $identifier
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
 * @property-read DealerLocationSalesTax $salesTax
 * @property-read QboLocationMapping $qboMapping
 * @property Region $locationRegion
 * @property NewDealerUser $dealer
 * @property User $user
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

    public const PAC_TYPE_PERCENT = 'percent';

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

    public const DEFAULT_SALES_TAX_ITEM_COLUMN_TITLES = [
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
        'location_id',
        'google_business_store_code'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sales_tax_item_column_titles' => 'array'
    ];

    /**
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(NewDealerUser::class, 'dealer_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
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

    public function inventoryExists(int $type_id): bool
    {
        return $this->dealer_location_id ?
            Inventory::where('dealer_location_id', $this->dealer_location_id)->where('entity_type_id', $type_id)->exists() : false;
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
     * Returns location identifier
     *
     * @return false|string
     */
    public function getIdentifierAttribute()
    {
        return CompactHelper::shorten($this->dealer_location_id);
    }

    /**
     * @return type
     */
    public function number()
    {
        return $this->belongsTo(Number::class, 'sms_phone', 'dealer_number');
    }

    /**
     * @return HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(DealerLocationQuoteFee::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * @return HasMany
     */
    public function mileageFees(): HasMany
    {
        return $this->hasMany(DealerLocationMileageFee::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * @return BelongsTo
     */
    public function locationRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region', 'region_code');
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

    /**
     * @return HasOne
     */
    public function qboMapping(): HasOne
    {
        return $this->hasOne(QboLocationMapping::class, 'dealer_location_id', 'dealer_location_id');
    }

    /**
     * Return Whatever License Number We Can Find
     *
     * @return string
     */
    public function getLicenseNumberAttribute(): string {
        // Get Dealer License Number
        $licenseNo = $this->dealer_license_no;

        // Get Federal Number
        if(empty($licenseNo)) {
            $licenseNo = $this->federal_id;
        }

        // Get State License Number
        if(empty($licenseNo)) {
            $licenseNo = $this->state_issued;
        }

        // Get County License Number
        if(empty($licenseNo)) {
            $licenseNo = $this->county_issued;
        }

        // Return License Number
        return $licenseNo ?: '';
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function boot(): void
    {
        parent::boot();

        self::observe(app()->make(DealerLocationObserver::class));
    }

    public function getCityRegionAttribute(): string
    {
        $locationAddr = $this->city;

        if (!empty($locationAddr) && !empty($this->region)) {
            $locationAddr .= ', ';
        }

        return $locationAddr . $this->region;
    }

    public function getCityStateAttribute(): string
    {
        $locationAddr = $this->city;

        if (!empty($locationAddr) && !empty($this->region)) {
            $locationAddr .= ', ';
        }

        $state = $this->region;
        if(isset(GeographyHelper::STATES_LIST[$this->region])) {
            $baseState = GeographyHelper::STATES_LIST[$this->region];
            $state = ucwords(strtolower($baseState));
        }

        return $locationAddr . $state;
    }

    public function getLocationTitleAttribute(): string
    {
        $locationAddr = $this->city;

        if (!empty($locationAddr) && !empty($this->region)) {
            $locationAddr .= ', ';
        }

        $locationAddr .= $this->region;

        $locationTitle = $this->name;
        if (!empty($locationAddr)) {
            $locationTitle .= ' (' . $locationAddr . ')';
        }

        return $locationTitle;
    }

    public static function phoneWithNationalFormat(string $phone, string $countryCode = 'US'): string
    {
        try {
            return PhoneNumber::make($phone, strtoupper($countryCode))->formatNational();
        } catch (\Exception $exception) {
            return $phone;
        }
    }
}
