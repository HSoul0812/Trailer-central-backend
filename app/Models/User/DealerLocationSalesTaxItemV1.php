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
}
