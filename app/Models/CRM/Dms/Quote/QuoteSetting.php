<?php

namespace App\Models\CRM\Dms\Quote;

use App\Models\Traits\TableAware;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteSetting extends Model
{
    use TableAware;

    protected $table = 'dealer_quote_settings';

    public $timestamps = false;

    protected $fillable = [
        'include_inventory_for_sales_tax',
        'include_part_for_sales_tax',
        'include_labor_for_sales_tax',
        'include_fees_for_sales_tax',
        'default_sales_location_id',
        'local_calculation_enabled',
    ];

    protected $casts = [
        'include_inventory_for_sales_tax' => 'boolean',
        'include_part_for_sales_tax' => 'boolean',
        'include_labor_for_sales_tax' => 'boolean',
        'include_fees_for_sales_tax' => 'boolean',
    ];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function defaultSalesLocation(): HasOne
    {
        return $this->hasOne(DealerLocation::class, 'dealer_location_id', 'default_sales_location_id');
    }
}
