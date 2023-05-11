<?php

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class GoogleMapViewport
{
    public GoogleMapPosition $northeast;
    public GoogleMapPosition $southwest;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->northeast = GoogleMapPosition::fromData($data['northeast']);
        $obj->southwest = GoogleMapPosition::fromData($data['southwest']);

        return $obj;
    }
}
