<?php

namespace App\Transformers\User;

use App\Traits\CompactHelper;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;
use App\Models\User\DealerLocation;

class DealerLocationSimpleTransformer extends TransformerAbstract
{
    public function transform(DealerLocation $dealerLocation): array
    {
        return [
            'id' => $dealerLocation->dealer_location_id,
            'name' => $dealerLocation->name,
            'address' => $dealerLocation->address,
            'city' => $dealerLocation->city,
            'county' => $dealerLocation->county,
            'region' => $dealerLocation->region,
            'postalcode' => $dealerLocation->postalcode,
            'country' => $dealerLocation->country,
            'exists_type_1' => $dealerLocation->inventoryExists(1),
            'exists_type_2' => $dealerLocation->inventoryExists(2),
            'exists_type_3' => $dealerLocation->inventoryExists(3),
            'exists_type_4' => $dealerLocation->inventoryExists(4),
            'exists_type_5' => $dealerLocation->inventoryExists(5),
        ];
    }
}
