<?php

namespace App\Models\Inventory\Packages;

use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class PackageInventory
 * @package App\Models\Inventory\Packages
 *
 * @property int $id,
 * @property int $package_id,
 * @property int $inventory_id,
 * @property boolean $is_main_item,
 * @property \DateTimeInterface $created_at,
 * @property \DateTimeInterface $updated_at,
 */
class PackageInventory extends Pivot
{
    /**
     * @var string
     */
    protected $table = 'packages_inventory';

    protected $fillable = [
        'package_id',
        'inventory_id',
        'is_main_item',
    ];

    /**
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }
}
