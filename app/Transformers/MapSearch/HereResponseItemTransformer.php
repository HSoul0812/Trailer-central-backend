<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\HereResponseItem;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\TransformerAbstract;

class HereResponseItemTransformer extends TransformerAbstract
{
    #[ArrayShape(['address' => 'array', 'position' => 'array|null'])]
    public function transform(HereResponseItem $item): array
    {
        $address = $item->address;

        return [
            'address' => [
                'label' => $address->label,
                'countryCode' => $address->countryCode,
                'countryName' => $address->countryName,
                'stateCode' => $address->stateCode,
                'state' => $address->state,
                'county' => $address->county,
                'city' => $address->city ?? null,
                'district' => $address->district ?? null,
                'street' => $address->street ?? null,
                'postalCode' => $address->postalCode ?? null,
            ],
            'position' => isset($item->position)
                ? [
                    'lat' => $item->position->lat,
                    'lng' => $item->position->lng,
                ]
                : null,
        ];
    }
}
