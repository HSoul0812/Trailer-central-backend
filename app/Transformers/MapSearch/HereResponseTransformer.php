<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\HereResponse;
use League\Fractal\TransformerAbstract;

class HereResponseTransformer extends TransformerAbstract
{
    public function transform(HereResponse $response): array
    {
        $itemTransformer = new HereResponseItemTransformer();
        $data = [];
        foreach ($response->items as $item) {
            $data[] = $itemTransformer->transform($item);
        }

        return $data;
    }
}
