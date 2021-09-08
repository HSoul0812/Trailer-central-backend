<?php

declare(strict_types=1);

namespace App\Services\CRM\User;

use App\DTO\CRM\Users\TimeClockQueryResult;
use App\Models\CRM\User\TimeClock;

interface TimeClockServiceInterface
{
    /**
     * Starts/stops the clock for given employee
     *
     * @param  int  $employeeId
     * @return TimeClock
     */
    public function punch(int $employeeId): TimeClock;

    /**
     * Retrieves the tracking log and the summary for given employee and time frame
     *
     * @param  int  $employeeId
     * @param  string|null  $fromDate  starting date
     * @param  string|null  $toDate  ending date
     *
     * @return TimeClockQueryResult
     */
    public function trackingByEmployee(int $employeeId, ?string $fromDate, ?string $toDate): TimeClockQueryResult;
}
