<?php

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcApiResponseBrand;
use League\Fractal\TransformerAbstract;

class BrandTransformer extends TransformerAbstract
{
    /**
     * @param TcApiResponseBrand $brand
     * @return array
     */
    public function transform($brand): array
    {
        return [
            'id'   => $brand->id,
            'name' => $brand->name,
        ];
    }
}
