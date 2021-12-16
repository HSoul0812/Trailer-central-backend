<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Models\Inventory\Image;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(Image $image): array
    {
        return [
            'image_id' => $image->image_id,
            'url' => config('marketing.fb.settings.images.domain') . $image->filename,
            'noverlay' => $image->filename_noverlay ? config('marketing.fb.settings.images.domain') . $image->filename_noverlay : ''
        ];
    }
}
