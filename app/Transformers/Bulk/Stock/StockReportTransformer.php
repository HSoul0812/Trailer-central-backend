<?php

declare(strict_types=1);

namespace App\Transformers\Bulk\Stock;

use League\Fractal\TransformerAbstract;

class StockReportTransformer extends TransformerAbstract
{
    public function transform(array $transaction): array
    {
        return $transaction;
    }
}
