<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Inventory\InventoryImage;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(InventoryImage $invImage)
    {
        return [
            'id' => $invImage->image->id,
            'file' => $invImage->image->image->filename,
            'created_at' => $invImage->image->created_at,
            'updated_at' => $invImage->image->updated_at,
            'position' => $invImage->position
        ];
    }
}