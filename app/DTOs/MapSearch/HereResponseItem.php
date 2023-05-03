<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class HereResponseItem
{
    public string $title;
    public HereAddress $address;
    public ?HerePosition $position;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->title = $data['title'];
        $obj->address = HereAddress::fromData($data['address']);
        $obj->position = isset($data['position']) ? HerePosition::fromData($data['position']) : null;

        return $obj;
    }
}
