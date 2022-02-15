<?php

declare(strict_types=1);

namespace App\Transformers\Dms\Customer;

use App\Models\Inventory\Inventory;
use App\Transformers\Inventory\InventoryTransformer;

class CustomerInventoryTransformer extends InventoryTransformer
{
    public function transform(Inventory $inventory): array
    {
        $extendedProperties = $inventory->getAttribute('customer_inventory_id') ? [
            'customer_inventory_id' => $inventory->getAttribute('customer_inventory_id')
        ] : [];

        return array_merge(parent::transform($inventory), $extendedProperties);
    }
}
