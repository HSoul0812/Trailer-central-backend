<?php

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\GoogleGeocodeResponseItem;
use JetBrains\PhpStorm\ArrayShape;

class GoogleGeocodeResponseItemTransformer
{
    #[ArrayShape(['address' => "array", 'position' => "array"])]
    public function transform(GoogleGeocodeResponseItem $response): array
    {
        $data = [];
        $address = [];
        foreach($response->address_components as $c) {
            $address[$c->types[0]] = $c;
        }

        return [
            'address' => [
                'label'       => $response->formatted_address,
                'countryCode' => $address['country']?->short_name,
                'countryName' => $address['country']?->long_name,
                'stateCode'   => $address['administrative_area_level_1']?->short_name,
                'state'       => $address['administrative_area_level_1']?->long_name,
                'county'      => $address['administrative_area_level_2']?->short_name,
                'city'        => $address['locality']?->short_name,
                'district'    => $address['sublocality']?->short_name,
                'street'      => "{$address['route']?->short_name} {$address['route']?->short_name}",
                'postalCode'  => $address['postal_code']?->short_name,
            ],
            'position' => [
                'lat' => $response->geometry->location->lat,
                'lng' => $response->geometry->location->lng,
            ]
        ];
    }
}
