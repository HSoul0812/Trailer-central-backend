<?php

namespace App\Transformers\MapSearch;

use App\DTOs\MapSearch\GoogleAutocompleteResponseItem;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\TransformerAbstract;

class GoogleAutocompleteResponseItemTransformer extends TransformerAbstract
{
    #[ArrayShape(['address' => 'array'])]
    public function transform(GoogleAutocompleteResponseItem $response): array
    {
        return [
            'address' => [
                'label' => $response->description,
            ],
        ];
    }
}
