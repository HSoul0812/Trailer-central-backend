<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\TomTomGeocodeResponseItem;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\TransformerAbstract;

class TomTomGeocodeResponseItemTransformer extends TransformerAbstract
{
    #[ArrayShape(['address' => 'array', 'position' => 'array|null'])]
    public function transform(TomTomGeocodeResponseItem $item): array
    {
        $address = $item->address;

        return [
            'address' => [
                'label' => $address->freeformAddress,
                'countryCode' => $address->countryCode,
                'countryName' => $address->country,
                'stateCode' => $address->countrySubdivision,
                'state' => $address->countrySubdivisionName,
                'county' => $address->countrySecondarySubdivision,
                'city' => $address->municipality,
                'district' => $address->municipalitySubdivision,
                'street' => $address->streetName,
                'postalCode' => $address->postalCode,
            ],
            'position' => isset($item->position)
                ? [
                    'lat' => $item->position->lat,
                    'lng' => $item->position->lon,
                ]
                : null,
        ];
    }
}
