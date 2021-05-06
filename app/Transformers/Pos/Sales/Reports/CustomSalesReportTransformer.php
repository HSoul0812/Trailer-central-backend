<?php

declare(strict_types=1);

namespace App\Transformers\Pos\Sales\Reports;

use League\Fractal\TransformerAbstract;
use stdClass;

class CustomSalesReportTransformer extends TransformerAbstract
{
    public function transform(stdClass $transaction): array
    {
        return array_merge(
            get_object_vars($transaction),
            [
                'links' => explode(',', (string)$transaction->links),
                'taxes_amount' => (float)$transaction->taxes_amount,
                'price' => (float)$transaction->price,
                'profit' => (float)$transaction->profit,
                'refund' => (float)$transaction->refund
            ]
        );
    }
}
