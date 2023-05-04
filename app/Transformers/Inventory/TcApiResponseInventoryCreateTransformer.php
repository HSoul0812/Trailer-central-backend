<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcApiResponseInventoryCreate;
use League\Fractal\TransformerAbstract;

class TcApiResponseInventoryCreateTransformer extends TransformerAbstract
{
    public function transform(TcApiResponseInventoryCreate $inventory): array
    {
        return [
            'id' => (int) $inventory->id,
         ];
    }
}
