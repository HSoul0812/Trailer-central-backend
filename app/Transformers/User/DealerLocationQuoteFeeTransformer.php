<?php

declare(strict_types=1);

namespace App\Transformers\User;

use App\Models\User\DealerLocationQuoteFee;
use League\Fractal\TransformerAbstract;

class DealerLocationQuoteFeeTransformer extends TransformerAbstract
{
    public function transform(DealerLocationQuoteFee $fee): array
    {
        return $fee->toArray();
    }
}
