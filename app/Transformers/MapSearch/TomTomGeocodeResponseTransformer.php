<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\TomTomGeocodeResponse;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;

class TomTomGeocodeResponseTransformer extends TransformerAbstract
{
    #[Pure]
    public function transform(TomTomGeocodeResponse $response): array
    {
        $itemTransformer = new TomTomGeocodeResponseItemTransformer();
        $data = [];
        foreach ($response->results as $result) {
            $data[] = $itemTransformer->transform($result);
        }

        return $data;
    }
}
