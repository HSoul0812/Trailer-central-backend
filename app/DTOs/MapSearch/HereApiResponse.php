<?php

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class HereApiResponse
{
    public array $items;

    #[Pure] public static function fromData(array $data): self {
        $obj = new self();
        $obj->items = [];
        foreach($data['items'] as $item) {
            $obj->items[] = HereApiResponseItem::fromData($item);
        }
        return $obj;
    }
}
