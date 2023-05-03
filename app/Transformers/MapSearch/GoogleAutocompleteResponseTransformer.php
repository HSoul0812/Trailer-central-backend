<?php

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\GoogleAutocompleteResponse;
use JetBrains\PhpStorm\Pure;
use League\Fractal\TransformerAbstract;

class GoogleAutocompleteResponseTransformer extends TransformerAbstract
{
    #[Pure]
    public function transform(GoogleAutocompleteResponse $response): array
    {
        $itemTransformer = new GoogleAutocompleteResponseItemTransformer();
        $data = [];
        foreach ($response->predictions as $result) {
            $data[] = $itemTransformer->transform($result);
        }

        return $data;
    }
}
