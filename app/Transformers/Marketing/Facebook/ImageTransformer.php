<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Marketing\Facebook\Image;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(Image $image)
    {
        // Return Mapping
        return [
            'id' => $image->id,
            'file' => !empty($image->image) ? $image->image->filename : null,
            'created_at' => $image->created_at,
            'updated_at' => $image->updated_at
        ];
    }
}