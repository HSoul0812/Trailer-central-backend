<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryImage;

class InventoryImageTransformer extends MediaFileTransformer
{
    public function transform(InventoryImage $inventoryImage): array
    {
        $position = $inventoryImage->position ?? InventoryImage::LAST_IMAGE_POSITION;

        return [
            'image_id' => $inventoryImage->image_id,
            'is_default' => $inventoryImage->is_default,
            'is_secondary' => $inventoryImage->is_secondary,
            'position' => $inventoryImage->isDefault() ? InventoryImage::FIRST_IMAGE_POSITION : $position,
            'url' => $this->getBaseUrl() . $inventoryImage->image ? $inventoryImage->image->filename : '',
        ];
    }
}
