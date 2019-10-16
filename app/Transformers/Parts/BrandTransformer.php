<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Brand;

class BrandTransformer extends TransformerAbstract
{
    public function transform(Brand $brand)
    {                
	 return [
             'id' => (int)$brand->id,
             'name' => $brand->name
         ];
    }
}