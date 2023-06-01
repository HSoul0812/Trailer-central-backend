<?php

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcApiResponseManufacturer;
use League\Fractal\TransformerAbstract;

class ManufacturerTransformer extends TransformerAbstract
{
    /**
     * @param TcApiResponseManufacturer $manufacturer
     * @return array
     */
    public function transform($manufacturer): array
    {
        return [
            'id'   => $manufacturer->id,
            'name' => $manufacturer->name,
            'label' => $manufacturer->label,
        ];
    }
}
