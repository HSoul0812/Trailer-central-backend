<?php

namespace App\Transformers\IpInfo;

use App\DTOs\IpInfo\City;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\TransformerAbstract;

class CityTransformer extends TransformerAbstract
{
    #[ArrayShape([
        'city' => 'string',
        'postalcode' => 'string',
        'state' => 'string',
        'stateISO' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
        'country' => 'string',
        'countryISO' => 'string',
        'network' => 'string',
    ])]
    public function transform(City $city): array
    {
        return $city->toArray();
    }
}
