<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class TomTomReverseGeocodeResponse
{
    public array $results;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->results = [];
        foreach ($data['addresses'] as $item) {
            $obj->results[] = TomTomReverseGeocodeResponseItem::fromData($item);
        }

        return $obj;
    }
}
