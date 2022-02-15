<?php

declare(strict_types=1);

namespace App\Transformers\Dms\ServiceOrder;

use App\Models\CRM\Dms\ServiceOrder\MonthlyServiceHours;
use League\Fractal\TransformerAbstract;

class MonthlyHoursReportTransformer extends TransformerAbstract
{
    public function transform(MonthlyServiceHours $transaction): array
    {
        return $transaction->asArray();
    }
}
