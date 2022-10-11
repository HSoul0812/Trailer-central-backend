<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use stdClass;

class InventoryElasticSearchOutputTransformer extends TransformerAbstract
{
    public function transform(stdClass $hit): array
    {
        return (array)$hit->_source;
    }
}
