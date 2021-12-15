<?php


namespace App\Transformers\MapSearch;


use League\Fractal\TransformerAbstract;

class HereMapSearchItemTransformer extends TransformerAbstract
{
    public function transform($item) {
        $address = $item->address;
        return [
            'address' => [
                'label'=> $address->label,
                'countryCode' => $address->countryCode,
                'countryName' => $address->countryName,
                'stateCode' => $address->stateCode,
                'state' => $address->state,
                'county' => $address->county,
                'city' => $address->city ?? null,
                'district' => $address->district ?? null,
                'street' => $address->street ?? null,
                'postalCode' => $address->postalCode ?? null
            ],
            'position' => isset($item->position)
                ? [
                    'lat' => $item->position->lat,
                    'lng' => $item->position->lng
                ]
                : null
        ];
    }
}
