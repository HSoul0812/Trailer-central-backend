<?php

namespace App\DTOs\MapSearch;

class GoogleGeocodeResponse
{
    public array $results;

    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->results = [];
        foreach ($data['results'] as $item) {
            $obj->results[] = GoogleGeocodeResponseItem::fromData($item);
        }

        return $obj;
    }
}
