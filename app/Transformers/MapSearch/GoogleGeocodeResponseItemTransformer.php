<?php

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\GoogleGeocodeResponseItem;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\TransformerAbstract;

class GoogleGeocodeResponseItemTransformer extends TransformerAbstract
{
    #[ArrayShape(['address' => 'array', 'position' => 'array'])]
    public function transform(GoogleGeocodeResponseItem $response): array
    {
        $address = [];
        foreach ($response->address_components as $c) {
            $types = array_diff($c->types, ['political']);
            foreach ($types as $type) {
                $address[$type] = $c;
            }
        }
        $street_number = $address['street_number']->short_name ?? null;
        $route = $address['route']->short_name ?? null;

        return [
            'address' => [
                'label' => $response->formatted_address,
                'countryCode' => $address['country']->short_name ?? null,
                'countryName' => $address['country']->long_name ?? null,
                'stateCode' => $address['administrative_area_level_1']->short_name ?? null,
                'state' => $address['administrative_area_level_1']->long_name ?? null,
                'county' => $address['administrative_area_level_2']->short_name ?? null,
                'city' => $address['locality']->short_name ?? null,
                'district' => $address['sublocality']->short_name ?? null,
                'street' => "$street_number $route",
                'postalCode' => $address['postal_code']->short_name ?? null,
            ],
            'position' => [
                'lat' => $response->geometry->location->lat,
                'lng' => $response->geometry->location->lng,
            ],
        ];
    }
}
