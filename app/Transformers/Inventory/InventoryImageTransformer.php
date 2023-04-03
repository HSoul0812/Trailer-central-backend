<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryImage;

class InventoryImageTransformer extends MediaFileTransformer
{
    public function transform(InventoryImage $inventoryImage): array
    {
        $position = $inventoryImage->position ?? InventoryImage::LAST_IMAGE_POSITION;
        $inventory = $inventoryImage->inventory;

        $originalImageUrl = ''; // it will be always the original image (without overlay)
        $overlayUrl = ''; // it could be an overlay image

        if (is_object($inventoryImage->image)) {
            $originalImageUrl = $this->getBaseUrl().$this->originalImageUrl($inventory->overlay_enabled,$inventoryImage);
            $overlayUrl = $this->getBaseUrl().$inventoryImage->image->filename;
        }

        return [
            'image_id' => $inventoryImage->image_id,
            'is_default' => $inventoryImage->is_default,
            'is_secondary' => $inventoryImage->is_secondary,
            'position' => $inventoryImage->isDefault() ? InventoryImage::FIRST_IMAGE_POSITION : $position,
            'url' => $overlayUrl,
            'original_url' => $originalImageUrl
        ];
    }

    /**
     * @param null|int $typeOfOverlay
     * @param InventoryImage $inventoryImage
     * @return string|null
     */
    private function originalImageUrl(?int $typeOfOverlay, InventoryImage $inventoryImage): ?string
    {
        return $inventoryImage->originalFilenameRegardingInventoryOverlayConfig($typeOfOverlay);
    }
}
