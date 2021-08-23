<?php

declare(strict_types=1);

namespace App\Services\CRM\User;

use App\DTO\CRM\Users\TimeClockQueryResult;
use App\Repositories\CRM\User\TimeClockRepositoryInterface;
use App\Models\CRM\User\TimeClock;
use Illuminate\Support\Facades\Date;

class TimeClockService implements TimeClockServiceInterface
{
    /** @var TimeClockRepositoryInterface */
    protected $repository;

    public function __construct(TimeClockRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Starts/stops the clock for given employee
     *
     * @param  int  $employeeId
     * @return TimeClock
     */
    public function punch(int $employeeId): TimeClock
    {
        if ($this->repository->isClockTicking($employeeId)) {
            return $this->repository->markPunchOut($employeeId);
        }

        return $this->repository->markPunchIn($employeeId);
    }

    /**
     * Retrieves the tracking log and the summary for given employee and time frame
     *
     * @param  int  $employeeId
     * @param  string|null  $fromDate  starting date
     * @param  string|null  $toDate  ending date
     *
     * @return TimeClockQueryResult
     */
    public function trackingByEmployee(int $employeeId, ?string $fromDate, ?string $toDate): TimeClockQueryResult
    {
        $fromDate = $fromDate ?? Date::now()->format('Y-m-d');
        $toDate = $toDate ? $toDate.' 23:59:59' : Date::now()->format('Y-m-d 23:59:59');

        return TimeClockQueryResult::result([
                'log' => $this->repository->find([
                    'employee_id' => $employeeId,
                    'from_date' => $fromDate,
                    'to_date' => $toDate
                ]),
                'summary' => $this->repository->summary([
                    'employee_id' => $employeeId,
                    'from_date' => $fromDate,
                    'to_date' => $toDate
                ])
            ]
        );
    }
}
