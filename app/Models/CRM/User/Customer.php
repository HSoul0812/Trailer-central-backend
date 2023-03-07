<?php

namespace App\Models\CRM\User;

use App\Domains\Scout\Traits\ExceptionableSearchable;
use App\Helpers\StringHelper;
use App\Models\Traits\TableAware;
use ElasticScoutDriverPlus\CustomSearch;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\UnitSale;
use App\Models\Region;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\User\User as Dealer;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use Carbon\Carbon;

/**
 * Class Customer
 * @package App\Models\CRM\User
 *
 * @property $id
 * @property $dealer_id
 * @property $first_name
 * @property $last_name
 * @property $display_name
 * @property $email
 * @property $drivers_license
 * @property $home_phone
 * @property $work_phone
 * @property $cell_phone
 * @property $address
 * @property $city
 * @property $region
 * @property $postal_code
 * @property $country
 * @property $website_lead_id
 * @property $tax_exempt
 * @property $is_financing_company
 * @property $account_number
 * @property $qb_id
 * @property $gender
 * @property $dob
 * @property $deleted_at
 * @property $is_wholesale
 * @property $default_discount_percent
 * @property $middle_name
 * @property $company_name
 * @property $use_same_address
 * @property $shipping_address
 * @property $shipping_city
 * @property $shipping_region
 * @property $shipping_postal_code
 * @property $shipping_country
 * @property $county
 * @property $shipping_county
 *
 * @property-read Dealer $dealer
 */
class Customer extends Model
{
    use ExceptionableSearchable, CustomSearch, SoftDeletes, TableAware;

    protected $table = 'dms_customer';

    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'email',
        'drivers_license',
        'home_phone',
        'work_phone',
        'cell_phone',
        'address',
        'city',
        'region',
        'postal_code',
        'country',
        'website_lead_id',
        'tax_exempt',
        'is_financing_company',
        'account_number',
        'gender',
        'dob',
        'deleted_at',
        'is_wholesale',
        'default_discount_percent',
        'middle_name',
        'company_name',
        'use_same_address',
        'shipping_address',
        'shipping_city',
        'shipping_region',
        'shipping_postal_code',
        'shipping_country',
        'county',
        'shipping_county',
        'qb_id'
    ];

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setCompanyNameAttribute(?string $value): void
    {
        $this->attributes['company_name'] = StringHelper::trimWhiteSpaces($value);
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setFirstNameAttribute(?string $value): void
    {
        $this->attributes['first_name'] = StringHelper::trimWhiteSpaces($value);
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setMiddleNameAttribute(?string $value): void
    {
        $this->attributes['middle_name'] = StringHelper::trimWhiteSpaces($value);
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setLastNameAttribute(?string $value): void
    {
        $this->attributes['last_name'] = StringHelper::trimWhiteSpaces($value);
    }

    /**
     * @param string|null $value
     *
     * @return void
     */
    public function setDisplayNameAttribute(?string $value): void
    {
        $this->attributes['display_name'] = StringHelper::trimWhiteSpaces($value);
    }

    public function quotes()
    {
        return $this->hasMany(UnitSale::class, 'buyer_id', 'id');
    }

    public function openQuotes()
    {
        return $this->quotes()->where('is_archived', 0)
            ->where(function ($query) {
                $query->whereDoesntHave('payments')
                    ->orWhereHas('payments', function ($query) {
                        $query->select(DB::raw('sum(amount) as paid_amount'))
                            ->groupBy('invoice_id')
                            ->havingRaw('paid_amount < dms_unit_sale.total_price or paid_amount <= 0');
                    });
            });
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Region Name
     *
     * @return BelongsTo
     */
    public function regionName(): BelongsTo {
        return $this->belongsTo(Region::class, 'region', 'region_name');
    }

    /**
     * Region Code
     *
     * @return BelongsTo
     */
    public function regionCode(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region', 'region_code');
    }

    public function searchableAs()
    {
        return 'customers';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array['dealer_id'] = (int)$array['dealer_id'];
        $array['tax_exempt'] = (int)$array['tax_exempt'];
        $array['is_wholesale'] = (int)$array['is_wholesale'];

        return $array;
    }


    /**
     * Returns the customer full name
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Returns the customer display or full name
     *
     * @return string
     */
    public function getDisplayFullNameAttribute(): ?string
    {
        return $this->display_name ?: $this->full_name;
    }

    /**
     * Returns the customer age in years
     *
     * @return int
     */
    public function getAgeAttribute() : int
    {
        return (int)Carbon::parse($this->dob)->diff(Carbon::now())->format('%y');
    }

    /**
     * Returns the customer birth month name
     *
     * @return string
     */
    public function getBirthMonthAttribute() : string
    {
        return Carbon::parse($this->dob)->format('F');
    }

    /**
     * Returns the region code for the customer address
     *
     * @return string
     */
    public function getRegionCodeAttribute() : string
    {
        if(strlen($this->region) < 3) {
            return $this->region;
        }
        return $this->regionName->region_code ?? '';
    }

    public function getOwnedUnitsAttribute() : Collection
    {
        $inventoryRepo = app(InventoryRepositoryInterface::class);
        return $inventoryRepo->getAll(['customer_id' => $this->id], false);
    }

    public function lead() : HasOne
    {
        return $this->hasOne(Lead::class, 'identifier', 'website_lead_id');
    }
}
