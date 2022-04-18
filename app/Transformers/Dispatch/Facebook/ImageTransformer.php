<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Models\Inventory\InventoryImage;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(InventoryImage $invImage): array
    {
        // Get Filename
        $file = '';
        if(!empty($invImage->image)) {
            if(!empty($invImage->image->filename_noverlay)) {
                $file = $invImage->image->filename_noverlay;
            } else {
                $file = $invImage->image->filename;
            }
        }

        // Return Mapping
        return [
            'image_id' => $invImage->image_id,
            'url' => config('marketing.fb.settings.images.domain') . $file,
            'position' => $invImage->position
        ];
    }
}
