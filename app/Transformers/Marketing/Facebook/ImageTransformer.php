<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Marketing\Facebook\Image;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(Image $image)
    {
        // Get Filename
        $file = '';
        if(!empty($image->image)) {
            $file = $image->image->filename_noverlay ?? $image->image->filename;
        }

        // Return Mapping
        return [
            'id' => $image->id,
            'file' => $file,
            'created_at' => $image->created_at,
            'updated_at' => $image->updated_at
        ];
    }
}