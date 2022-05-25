<?php

declare(strict_types=1);

namespace App\Models\User;

use Illuminate\Database\Query\Builder;

/**
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class DealerLocationSalesTaxItemV1 extends DealerLocationSalesTaxItem
{
    protected $table = 'dealer_location_sales_tax_item';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_location_id",
        "item_type",
        "tax_pct",
        "tax_cap",
        "standard",
        "tax_exempt",
        "out_of_state_reciprocal",
        "out_of_state_non_reciprocal"
    ];
}
