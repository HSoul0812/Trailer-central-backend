<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class TomTomGeocodeResponse
{
    public array $results;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->results = [];
        foreach ($data['results'] as $item) {
            $obj->results[] = TomTomGeocodeResponseItem::fromData($item);
        }

        return $obj;
    }
}
