<?php

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\GoogleGeocodeResponse;

class GoogleGeocodeResponseTransformer
{
    public function transform(GoogleGeocodeResponse $response): array
    {
        $itemTransformer = new GoogleGeocodeResponseItemTransformer();
        $data = [];
        foreach ($response->results as $result) {
            $data[] = $itemTransformer->transform($result);
        }

        return $data;
    }
}
