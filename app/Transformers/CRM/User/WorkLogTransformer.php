<?php

declare(strict_types=1);

namespace App\Transformers\CRM\User;

use Illuminate\Support\Facades\Date;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\TimeClock;

class WorkLogTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = [];

    public function transform($log): array
    {
        return [
            'ro_id' => $log->user_defined_id,
            'date' => Date::parse($log->start_date)->format('Y-m-d'),
            'start' => $log->start_date,
            'end' => $log->completed_date,
            'labor_code' => $log->labor_code,
            'hourly_rate' => $log->hourly_rate,
            'billed_hours' => $log->billed_hrs,
            'paid_hours' => $log->paid_hrs,
        ];
    }
}
