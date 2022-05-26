<?php

namespace App\Models\User;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class DealerLocationSalesTaxItem extends Model
{
    use TableAware;

    protected $table = 'dealer_location_sales_tax_item_v2';

    const TYPE_STATE = 'state';
    const TYPE_COUNTY = 'county';
    const TYPE_CITY = 'city';
    const TYPE_DISTRICT1 = 'district1';
    const TYPE_DISTRICT2 = 'district2';
    const TYPE_DISTRICT3 = 'district3';
    const TYPE_DISTRICT4 = 'district4';
    const TYPE_DMV = 'dmv';
    const TYPE_REGISTRATION = 'registration';
    const REGISTRATION_TITLE = 'Registration Pcnt';

    public static $types = [
        self::TYPE_STATE => 'State',
        self::TYPE_COUNTY => 'County',
        self::TYPE_CITY => 'City',
        self::TYPE_DISTRICT1 => 'District1',
        self::TYPE_DISTRICT2 => 'District2',
        self::TYPE_DISTRICT3 => 'District3',
        self::TYPE_DISTRICT4 => 'District4',
        self::TYPE_DMV => 'Dmv',
        self::TYPE_REGISTRATION => 'Registration',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_location_id",
        "entity_type_id",
        "item_type",
        "tax_pct",
        "tax_cap",
        "standard",
        "tax_exempt",
        "out_of_state_reciprocal",
        "out_of_state_non_reciprocal",
        "registration_title"
    ];
}
