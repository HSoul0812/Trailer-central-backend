<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

class InventoryElasticSearchOutputTransformer
{
    public function transform(array $hint): array
    {
        return $hint;
    }
}
