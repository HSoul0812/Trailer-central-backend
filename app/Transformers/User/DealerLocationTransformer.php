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
            'contact' => $dealerLocation->contact,
            'website' => $dealerLocation->website,
            'phone' => $dealerLocation->phone,
            'fax' => $dealerLocation->fax,
            'email' => $dealerLocation->email,
            'address' => $dealerLocation->address,
            'city' => $dealerLocation->city,
            'county' => $dealerLocation->county,
            'region' => $dealerLocation->region,
            'postal' => $dealerLocation->postalcode,
            'country' => $dealerLocation->country,
            'federal_id' => $dealerLocation->federal_id,
            'sales_tax' => $dealerLocation->salesTax,
            'dealer_location_no' => $dealerLocation->dealer_license_no,
            'federal_id' => $dealerLocation->federal_id
        ];
    }
}
