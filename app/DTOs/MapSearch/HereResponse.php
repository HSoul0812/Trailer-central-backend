<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class HereResponse
{
    public array $items;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->items = [];
        foreach ($data['items'] as $item) {
            $obj->items[] = HereResponseItem::fromData($item);
        }

        return $obj;
    }
}
