<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Marketing\Facebook\Image;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(Image $image)
    {
        return [
            'id' => $image->id,
            'file' => $image->image->filename,
            'created_at' => $image->created_at,
            'updated_at' => $image->updated_at
        ];
    }
}