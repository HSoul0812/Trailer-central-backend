<?php

namespace App\Transformers\Location;

use App\DTOs\User\TcApiResponseUserLocation;
use League\Fractal\TransformerAbstract;

class TcApiResponseUserLocationTransformer extends TransformerAbstract
{
    public function transform(TcApiResponseUserLocation $location): array
    {
        return [
            'id' => $location->id,
            'identifier' => $location->identifier,
            'name' => $location->name,
            'contact' => $location->contact,
            'address' => $location->address,
            'city' => $location->city,
            'county' => $location->county,
            'region' => $location->region,
            'country' => $location->country,
            'postalCode' => $location->postalCode,
            'phone' => $location->phone,
            'is_default' => $location->is_default,
        ];
    }
}
