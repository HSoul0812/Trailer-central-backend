<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class TomTomApiResponseItem
{
    public string $type;
    public ?string $entityType;
    public TomTomApiAddress $address;
    public ?TomTomApiPosition $position;

    #[Pure]
 public static function fromData(array $data): self
 {
     $obj = new self();
     $obj->type = $data['type'];
     $obj->entityType = $data['entityType'] ?? null;
     $obj->address = TomTomApiAddress::fromData($data['address']);
     $obj->position = isset($data['position']) ? TomTomApiPosition::fromData($data['position']) : null;

     return $obj;
 }
}
