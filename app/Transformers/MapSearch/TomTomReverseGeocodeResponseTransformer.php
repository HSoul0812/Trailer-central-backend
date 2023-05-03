<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\TomTomReverseGeocodeResponse;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;

class TomTomReverseGeocodeResponseTransformer extends TransformerAbstract
{
    #[Pure]
    public function transform(TomTomReverseGeocodeResponse $response): array
    {
        $itemTransformer = new TomTomReverseGeocodeResponseItemTransformer();
        $data = [];
        foreach ($response->results as $result) {
            $data[] = $itemTransformer->transform($result);
        }

        return $data;
    }
}
