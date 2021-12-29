<?php

namespace App\Transformers\Inventory\Manufacturers;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Manufacturers\Brand;

class BrandTransformer extends TransformerAbstract
{

    public function transform(Brand $brand)
    {
        return [
            'id' => $brand->brand_id,
            'name' => $brand->name
        ];
    }

}
