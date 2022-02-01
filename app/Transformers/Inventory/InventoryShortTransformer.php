<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\Inventory;
use League\Fractal\TransformerAbstract;

/**
 * Class InventoryShortTransformer
 *
 * @package App\Transformers\Inventory
 */
class InventoryShortTransformer extends TransformerAbstract
{
    /**
     * @param Inventory $inventory
     *
     * @return array
     */
    public function transform(Inventory $inventory): array
    {
        return [
            'id' => $inventory->inventory_id,
            'title' => $inventory->title,
            'vin' => $inventory->vin,
        ];
    }
}
