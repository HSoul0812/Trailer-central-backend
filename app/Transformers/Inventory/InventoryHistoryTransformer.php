<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\Models\Inventory\InventoryHistory;
use League\Fractal\TransformerAbstract;

class InventoryHistoryTransformer extends TransformerAbstract
{
    public function transform(InventoryHistory $transaction): array
    {
        return $transaction->asArray();
    }
}
