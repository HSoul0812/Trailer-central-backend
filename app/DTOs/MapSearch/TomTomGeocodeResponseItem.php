<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class TomTomGeocodeResponseItem
{
    public string $type;
    public ?string $entityType;
    public TomTomAddress $address;
    public ?TomTomPosition $position;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->type = $data['type'];
        $obj->entityType = $data['entityType'] ?? null;
        $obj->address = TomTomAddress::fromData($data['address']);
        $obj->position = isset($data['position']) ? TomTomPosition::fromData($data['position']) : null;

        return $obj;
    }
}
