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
        $inventories = [];

        foreach ($package->inventories as $inventory) {
            $inventories[] = [
                'inventory_id' => $inventory->inventory_id,
                'title' => $inventory->title,
                'is_main_item' => $inventory->pivot->is_main_item,
            ];
        }

        return [
            'id' => $package->id,
            'visible_with_main_item' => $package->visible_with_main_item,
            'inventories' => $inventories,
        ];
    }
}
