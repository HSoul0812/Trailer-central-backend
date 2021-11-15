<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\Image;

class ImageTransformer extends MediaFileTransformer
{
    public function transform(Image $image): array
    {
        return [
            'url' => $this->getBaseUrl().$image->filename
        ];
    }
}
