<?php

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class GoogleMapAddressComponent
{
    public string $long_name;
    public string $short_name;
    public array $types;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->long_name = $data['long_name'];
        $obj->short_name = $data['short_name'];
        $obj->types = $data['types'];

        return $obj;
    }
}
