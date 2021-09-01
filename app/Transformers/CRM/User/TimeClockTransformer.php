<?php

declare(strict_types=1);

namespace App\Transformers\CRM\User;

use Illuminate\Support\Facades\Date;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\User\TimeClock;

class TimeClockTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = ['employee'];

    public function transform(TimeClock $log): array
    {
        return [
            'id' => $log->id,
            'date' => Date::parse($log->punch_in)->format('Y-m-d'),
            'status' => $log->punch_out ? TimeClock::NOT_TICKING : TimeClock::TICKING,
            'start' => $log->punch_in,
            'end' => $log->punch_out,
            'labor' => 'RO '.$log->id // fake repair order number until we find a way to relate a RO to the log
        ];
    }

    public function includeEmployee(TimeClock $log): Primitive
    {
        return $this->primitive($log->employee, new EmployeeTransformer());
    }
}
