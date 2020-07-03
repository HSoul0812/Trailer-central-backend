<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Image;

class ImageTransformer extends TransformerAbstract {
    
    public function transform(Image $image) {
        return [
            'url' => env('AWS_URL').$image->filename
        ];
    }
}
