<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class HereApiResponseItem
{
    public string $title;
    public HereApiAddress $address;
    public ?HereApiPosition $position;

    #[Pure]
 public static function fromData(array $data): self
 {
     $obj = new self();
     $obj->title = $data['title'];
     $obj->address = HereApiAddress::fromData($data['address']);
     $obj->position = isset($data['position']) ? HereApiPosition::fromData($data['position']) : null;

     return $obj;
 }
}
