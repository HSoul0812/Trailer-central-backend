<?php

namespace App\Models\User;

use App\Models\Inventory\Inventory;
use App\Models\Traits\TableAware;
use App\Models\CRM\Text\Number;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * Class DealerLocation
 * @package App\Models\User
 *
 * @property int $dealer_location_id
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static DealerLocation findOrFail($id, array $columns = ['*'])
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
        "pac_type"
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

    /**
     * @return type
     */
    public function salesTax()
    {
        return $this->hasOne(DealerLocationSalesTax::class, 'dealer_location_id', 'dealer_location_id');
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

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('exclude_deleted', function (\Illuminate\Database\Eloquent\Builder $builder): void {

            $builder->whereNull('deleted_at');
        });
    }
}
