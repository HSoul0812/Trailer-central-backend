<?php

declare(strict_types=1);

namespace App\Transformers\MapSearch;

use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;

class HereMapSearchTransformer extends TransformerAbstract
{
    #[Pure] public function transform($searchResult): array
    {
        $itemTransformer = new HereMapSearchItemTransformer();
        $data = [];
        foreach ($searchResult->items as $item) {
            $data[] = $itemTransformer->transform($item);
        }

        return $data;
    }
}
