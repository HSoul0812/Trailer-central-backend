<?php

namespace App\Transformers\Inventory;

use App\Models\Image;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;

class InventoryImageTransformer extends MediaFileTransformer
{
    public function transform(InventoryImage $inventoryImage): array
    {
        $position = $inventoryImage->position ?? InventoryImage::LAST_IMAGE_POSITION;
        $inventory = $inventoryImage->inventory;
        $originalImageUrl = $this->originalImageUrl($inventory->overlay_enabled, $inventoryImage);

        return [
            'image_id' => $inventoryImage->image_id,
            'is_default' => $inventoryImage->is_default,
            'is_secondary' => $inventoryImage->is_secondary,
            'position' => $inventoryImage->isDefault() ? InventoryImage::FIRST_IMAGE_POSITION : $position,
            'url' => $this->getBaseUrl() . (is_object($inventoryImage->image) ? $inventoryImage->image->filename : ''),
            'original_url' => $this->getBaseUrl() . $originalImageUrl
        ];
    }

    /**
     * @param null|int $inventory_overlay_enabled
     * @param InventoryImage $inventoryImage
     * @return string|null
     */
    private function originalImageUrl(?int $inventory_overlay_enabled = null, InventoryImage $inventoryImage): ?string
    {
        if ($inventory_overlay_enabled == Inventory::OVERLAY_ENABLED_ALL) {
            return $inventoryImage->image->filename_noverlay ? $inventoryImage->image->filename_noverlay : $inventoryImage->image->filename;
        } elseif($inventory_overlay_enabled == Inventory::OVERLAY_ENABLED_PRIMARY && ($inventoryImage->is_default == 1 || $inventoryImage->position == 1))  {
            return $inventoryImage->image->filename_noverlay ? $inventoryImage->image->filename_noverlay : $inventoryImage->image->filename;
        }

        return $inventoryImage->image->filename;

    }
}
