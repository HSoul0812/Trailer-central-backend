<?php

namespace App\Transformers\Inventory\Packages;

use App\Models\Inventory\Packages\Package;
use League\Fractal\TransformerAbstract;

/**
 * Class PackageTransformer
 * @package App\Transformers\Inventory\Packages
 */
class PackageTransformer extends TransformerAbstract
{
    /**
     * @param Package $package
     * @return array
     */
    public function transform(Package $package): array
    {
        return [
            'id' => $package->id,
            'visible_with_main_item' => $package->visible_with_main_item,
            'inventories' => $package->packagesInventory->map(function ($item, $key) {
                return $item->only(['inventory_id', 'is_main_item']);
            }),
        ];
    }
}
