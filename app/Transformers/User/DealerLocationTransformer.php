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
            'name' => $dealerLocation->name,
            'county' => $dealerLocation->county,
            'phone' => $dealerLocation->phone,
            'federal_id' => $dealerLocation->federal_id,
            'sales_tax' => $dealerLocation->salesTax,
            'location_id' => $dealerLocation->location_id,
            'dealer_location_no' => $dealerLocation->dealer_license_no,
            'federal_id' => $dealerLocation->federal_id	
        ];
    }
}
