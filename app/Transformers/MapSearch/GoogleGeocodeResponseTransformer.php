<?php

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\GoogleGeocodeResponse;
use League\Fractal\TransformerAbstract;

class GoogleGeocodeResponseTransformer extends TransformerAbstract
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
