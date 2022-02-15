<?php

namespace App\Models\User;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

/**
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static DealerLocationSalesTax findOrFail($id, array $columns = ['*'])
 * @method static Model|Collection|static[]|static|null find($id, $columns = ['*'])
 * @method static Model|static updateOrCreate(array $attributes, array $values = [])
 *
 */
class DealerLocationSalesTax extends Model
{
    use TableAware;

    protected $table = 'dealer_location_sales_tax';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_location_id',
        'sales_tax_id',
        'labor_tax_type',
        'use_local_tax',
        'tax_before_trade',
        'taxed_on_total_of',
        'shop_supply_basis',
        'shop_supply_pct',
        'shop_supply_cap',
        'env_fee_basis',
        'env_fee_pct',
        'env_fee_cap',
        'is_sublet_taxed',
        'is_shop_supplies_taxed',
        'is_env_fee_taxed',
        'is_parts_on_service_taxed',
        'is_labor_on_service_taxed',
        'tax_calculator_id',
        'is_shipping_taxed'
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }
}
