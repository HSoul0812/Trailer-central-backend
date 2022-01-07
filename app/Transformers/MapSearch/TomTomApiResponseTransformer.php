<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\TomTomApiResponse;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;

class TomTomApiResponseTransformer extends TransformerAbstract
{
    #[Pure] public function transform(TomTomApiResponse $response): array
    {
        $itemTransformer = new TomTomApiResponseItemTransformer();
        $data = [];
        foreach ($response->results as $result) {
            $data[] = $itemTransformer->transform($result);
        }

        return $data;
    }
}
