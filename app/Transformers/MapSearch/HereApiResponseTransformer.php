<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\HereApiResponse;
use League\Fractal\TransformerAbstract;

class HereApiResponseTransformer extends TransformerAbstract
{
    public function transform(HereApiResponse $response): array
    {
        $itemTransformer = new HereApiResponseItemTransformer();
        $data = [];
        foreach ($response->items as $item) {
            $data[] = $itemTransformer->transform($item);
        }

        return $data;
    }
}
