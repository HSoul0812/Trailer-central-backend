<?php

namespace App\Transformers\Location;

use App\Models\User\Location\Geolocation;
use League\Fractal\TransformerAbstract;

class GeolocationTransformer extends TransformerAbstract
{
    public function transform(Geolocation $geolocation): array
    {
        return [
            'id' => $geolocation->id,
            'city' => $geolocation->city,
            'state' => $geolocation->state,
            'zip' => $geolocation->zip,
            'latitude' => $geolocation->latitude,
            'longitude' => $geolocation->longitude,
            'country' => $geolocation->country
        ];
    }
}
