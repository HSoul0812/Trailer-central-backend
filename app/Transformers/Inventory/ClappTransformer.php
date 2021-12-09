<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryClapp;
use League\Fractal\TransformerAbstract;

/**
 * Class ClappsTransformer
 * @package App\Transformers\Inventory
 */
class ClappTransformer extends TransformerAbstract
{
    /**
     * @param InventoryClapp $inventoryClapp
     * @return array
     */
    public function transform(InventoryClapp $inventoryClapp): array
    {
        return [
            'field' => $inventoryClapp->field,
            'value' => $inventoryClapp->value,
        ];
    }
}
