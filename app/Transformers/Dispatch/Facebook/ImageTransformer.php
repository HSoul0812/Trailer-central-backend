<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Models\Inventory\InventoryImage;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(InventoryImage $invImage): array
    {
        return [
            'image_id' => $invImage->image_id,
            'url' => config('marketing.fb.settings.images.domain') . $invImage->filename,
            'noverlay' => $invImage->filename_noverlay ? config('marketing.fb.settings.images.domain') . $invImage->filename_noverlay : '',
            'position' => $invImage->position
        ];
    }
}
