<?php

namespace App\DTOs\MapSearch;

class GoogleGeocodeResponseItem
{
    public array $types;
    public array $address_components;
    public string $formatted_address;
    public GoogleMapGeometry $geometry;
    public string $place_id;
    public ?GoogleMapPlusCode $plus_code;

    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->types = $data['types'];
        $obj->address_components = [];
        foreach ($data['address_components'] as $c) {
            $obj->address_components[] = GoogleMapAddressComponent::fromData($c);
        }
        $obj->formatted_address = $data['formatted_address'];
        $obj->geometry = GoogleMapGeometry::fromData($data['geometry']);
        $obj->place_id = $data['place_id'];
        $obj->plus_code = isset($data['plus_code'])
            ? GoogleMapPlusCode::fromData($data['plus_code'])
            : null;

        return $obj;
    }
}
