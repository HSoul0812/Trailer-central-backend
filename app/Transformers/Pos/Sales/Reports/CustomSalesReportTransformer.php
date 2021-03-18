<?php

declare(strict_types=1);

namespace App\Transformers\Pos\Sales\Reports;

use League\Fractal\TransformerAbstract;

class CustomSalesReportTransformer extends TransformerAbstract
{
    public function transform(array $transaction): array
    {
        return $transaction;
    }
}
