<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class TomTomReverseGeocodeResponseItem
{
    public TomTomAddress $address;
    public ?string $position;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->address = TomTomAddress::fromData($data['address']);
        $obj->position = $data['position'] ?? null;

        return $obj;
    }
}
