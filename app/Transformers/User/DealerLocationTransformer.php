<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\DealerLocation;

class DealerLocationTransformer extends TransformerAbstract 
{
    public function transform(DealerLocation $dealerLocation)
    {
	return [
            'id' => $dealerLocation->dealer_location_id,
            'name' => $dealerLocation->name
        ];
    }
}
