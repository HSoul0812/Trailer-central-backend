<?php

namespace App\DTOs\MapSearch;

class GoogleMapGeometry
{
    public GoogleMapPosition $location;
    public string $location_type;
    public GoogleMapViewport $viewport;

    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->location = GoogleMapPosition::fromData($data['location']);
        $obj->location_type = $data['location_type'];
        $obj->viewport = GoogleMapViewport::fromData($data['viewport']);

        return $obj;
    }
}
