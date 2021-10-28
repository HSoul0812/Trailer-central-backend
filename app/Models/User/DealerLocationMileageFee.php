<?php

namespace App\Models\User;

use App\Models\Inventory\Category;
use App\Models\Inventory\EntityType;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerLocationMileageFee extends Model
{
    use TableAware;

    protected $table = 'dealer_location_mileage_fee';
    protected $fillable = [
        'dealer_location_id',
        'inventory_category_id',
        'fee_per_mile',
    ];

    public function dealerLocation(): BelongsTo {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function entityType(): BelongsTo {
        return $this->belongsTo(EntityType::class, 'entity_type_id', 'entity_type_id');
    }

    public function inventoryCategory(): BelongsTo {
        return $this->belongsTo(Category::class, 'inventory_category_id', 'inventory_category_id');
    }
}
