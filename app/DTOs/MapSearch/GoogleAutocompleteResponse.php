<?php

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class GoogleAutocompleteResponse
{
    public array $predictions;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->predictions = [];
        foreach ($data['predictions'] as $item) {
            $obj->predictions[] = GoogleAutocompleteResponseItem::fromData($item);
        }

        return $obj;
    }
}
