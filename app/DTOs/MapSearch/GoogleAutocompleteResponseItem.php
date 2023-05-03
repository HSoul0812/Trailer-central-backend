<?php

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class GoogleAutocompleteResponseItem
{
    public string $description;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->description = $data['description'];

        return $obj;
    }
}
