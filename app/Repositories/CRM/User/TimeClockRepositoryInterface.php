<?php

namespace App\Repositories\CRM\User;

use App\DTO\CRM\Users\TimeClockSummary;
use App\Models\CRM\User\TimeClock;
use App\Repositories\GenericRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

interface TimeClockRepositoryInterface extends GenericRepository
{
    /**
     * Finds all tracking data according some filters.
     *
     * @param  array  $filters
     * @returns LengthAwarePaginator
     *
     * @throws InvalidArgumentException when neither 'employee_id' and 'dealer_id' were not provided
     * @throws InvalidArgumentException when 'from_date' was not provided
     * @throws InvalidArgumentException when 'to_date' was not provided
     * @throws InvalidArgumentException when date range was too wide
     */
    public function find(array $filters): LengthAwarePaginator;

    /**
     * Finds all tracking data according some filters.
     *
     * @param  array  $filters
     * @returns TimeClockSummary
     *
     * @throws InvalidArgumentException when neither 'employee_id' and 'dealer_id' were not provided
     * @throws InvalidArgumentException when 'from_date' was not provided
     * @throws InvalidArgumentException when 'to_date' was not provided
     * @throws InvalidArgumentException when date range was too wide
     */
    public function summary(array $filters): TimeClockSummary;

    /**
     * Checks if the employee has the clock ticking currently.
     *
     * @param  int  $employeeId
     * @return bool
     */
    public function isClockTicking(int $employeeId): bool;

    /**
     * Starts the clock for given employee
     *
     * @param  int  $employeeId
     * @return TimeClock
     */
    public function markPunchIn(int $employeeId): TimeClock;

    /**
     * Stops the clock for given employee
     *
     * @param  int  $employeeId
     * @return TimeClock
     */
    public function markPunchOut(int $employeeId): TimeClock;
}
