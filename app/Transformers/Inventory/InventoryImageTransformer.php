<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryImage;

class InventoryImageTransformer extends MediaFileTransformer
{
    public function transform(InventoryImage $inventoryImage): array
    {
        return [
            'image_id' => $inventoryImage->image_id,
            'is_default' => $inventoryImage->is_default,
            'is_secondary' => $inventoryImage->is_secondary,
            'position' => $inventoryImage->position ?: 100,
            'url' => $this->getBaseUrl() . $inventoryImage->image->filename,
        ];
    }
}
