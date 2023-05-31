<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;

class BrandTransformer extends TransformerAbstract
{
    public function transform($brand): array
    {
        return [
            'id' => (int) $brand['id'],
            'name' => $brand['name'],
        ];
    }
}
