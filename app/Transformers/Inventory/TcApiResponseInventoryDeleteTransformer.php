<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcApiResponseInventoryDelete;
use League\Fractal\TransformerAbstract;

class TcApiResponseInventoryDeleteTransformer extends TransformerAbstract
{
    public function transform(TcApiResponseInventoryDelete $response): array
    {
        return [
            'status' => (string) $response->status,
         ];
    }
}
