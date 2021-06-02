<?php

namespace App\Models\Inventory\Packages;

use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Package
 * @package App\Models\Inventory\Packages
 *
 * @property int $id,
 * @property int $dealer_id,
 * @property boolean $visible_with_main_item,
 * @property \DateTimeInterface $created_at,
 * @property \DateTimeInterface $updated_at,
 *
 * @property Collection<Inventory> $inventories
 * @property Collection<PackageInventory> $packagesInventory
 */
class Package extends Model
{
    /**
     * @var string
     */
    protected $table = 'packages';

    protected $fillable = [
        'dealer_id',
        'visible_with_main_item',
    ];

    /**
     * @return BelongsToMany
     */
    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'packages_inventory', 'package_id', 'inventory_id')
            ->withPivot('is_main_item')
            ->using(PackageInventory::class);
    }

    /**
     * @return HasMany
     */
    public function packagesInventory(): HasMany
    {
        return $this->hasMany(PackageInventory::class);
    }
}
