<?php

declare(strict_types=1);

namespace App\Repositories\CRM\User;

use App\DTO\CRM\Users\TimeClockSummary;
use App\Models\CRM\User\Employee;
use App\Models\CRM\User\TimeClock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class TimeClockRepository implements TimeClockRepositoryInterface
{
    /**
     * @param  array  $filters
     * @returns LengthAwarePaginator
     *
     * @throws InvalidArgumentException when neither 'employee_id' and 'dealer_id' were not provided
     * @throws InvalidArgumentException when 'from_date' was not provided
     * @throws InvalidArgumentException when 'to_date' was not provided
     * @throws InvalidArgumentException when date range was too wide
     */
    public function find(array $filters): LengthAwarePaginator
    {
        $this->minimumFiltersGuard($filters);

        if (!isset($filters['per_page'])) {
            $filters['per_page'] = 365;
        }

        $fromDate = Date::parse($filters['from_date']);
        $toDate = Date::parse($filters['to_date']);

        $timeClockTableName = TimeClock::getTableName();
        $employeeTableName = Employee::getTableName();

        // This is a workaround to set right timezone, it should be done as a global session parameter on
        // ./config/database.php, but we don't know the side effects, so for now it is enough
        // @todo remove this when the global configuration is being setting time-zone up
        DB::statement('SET time_zone = :time_zone', ['time_zone' => env('DB_TIMEZONE')]);

        $query = TimeClock::select($timeClockTableName.'.*');

        $query->leftJoin($employeeTableName, $employeeTableName.'.id', '=', 'employee_id');

        if (isset($filters['employee_id'])) {
            $query->where($timeClockTableName.'.employee_id', $filters['employee_id']);
        }

        if (isset($filters['dealer_id'])) {
            $query->where($employeeTableName.'.dealer_id', $filters['dealer_id']);
        }

        $query->where('punch_in', '>=', $fromDate->toDateString());
        $query->where('punch_out', '<=', $toDate->toDateTimeString());

        $paginator = $query->paginate($filters['per_page'])->appends($filters);

        // @todo remove this when the global configuration is being setting time-zone up
        DB::statement('SET time_zone = :time_zone', ['time_zone' => 'UTC']);

        return $paginator;
    }

    /**
     * @param  array  $filters
     * @returns TimeClockSummary
     *
     * @throws InvalidArgumentException when neither 'employee_id' and 'dealer_id' were not provided
     * @throws InvalidArgumentException when 'from_date' was not provided
     * @throws InvalidArgumentException when 'to_date' was not provided
     * @throws InvalidArgumentException when date range was too wide
     */
    public function summary(array $filters): TimeClockSummary
    {
        $this->minimumFiltersGuard($filters);

        $fromDate = Date::parse($filters['from_date']);
        $toDate = Date::parse($filters['to_date']);

        $primaryWhere = 'tc.`employee_id` = ?'.PHP_EOL;
        $primaryId = $filters['employee_id'];

        if (isset($filters['dealer_id'])) {
            $primaryWhere = 'e.dealer_id = ?'.PHP_EOL;
            $primaryId = $filters['dealer_id'];
        }

        // This is a workaround to set right timezone, it should be done as a global session parameter on
        // ./config/database.php, but we don't know the side effects, so for now it is enough
        // @todo remove this when the global configuration is being setting time-zone up
        DB::statement('SET time_zone = :time_zone', ['time_zone' => env('DB_TIMEZONE')]);

        $dailySQL = <<<SQL
                    SELECT *,
                             (SUM(TIMESTAMPDIFF(MINUTE, check_in, check_out)) - worked_time) AS break_time
                    FROM (
                               select tc.`employee_id`,
                                      DATE_FORMAT(tc.`punch_in`, '%Y-%m-%d')                              AS day,
                                      DATE_FORMAT(tc.`punch_in`, '%Y-%v')                                 AS week,
                                      SUM(TIMESTAMPDIFF(MINUTE, punch_in, COALESCE(tc.punch_out, NOW()))) AS worked_time,
                                      (SELECT MIN(subq1.punch_in)
                                       FROM `dealer_employee_time_clock` subq1
                                       WHERE tc.`employee_id` = subq1.employee_id
                                         AND DATE_FORMAT(subq1.`punch_in`, '%Y-%m-%d') =
                                             DATE_FORMAT(tc.`punch_in`, '%Y-%m-%d'))                      AS check_in,
                                      (SELECT MAX(COALESCE(subq2.punch_out, NOW()))
                                       FROM `dealer_employee_time_clock` subq2
                                       WHERE tc.`employee_id` = subq2.employee_id
                                         AND DATE_FORMAT(subq2.`punch_in`, '%Y-%m-%d') =
                                             DATE_FORMAT(tc.`punch_in`, '%Y-%m-%d'))                      AS check_out
                               from `dealer_employee_time_clock` tc
                                        left join `dealer_employee` e on e.`id` = tc.`employee_id`
                               where $primaryWhere
                                 and tc.`punch_in` >= ?
                                 and tc.`punch_out` <= ?
                               group by tc.`employee_id`, DATE_FORMAT(tc.`punch_in`, '%Y-%v'),
                                        DATE_FORMAT(tc.`punch_in`, '%Y-%m-%d')
                           ) AS results
                      GROUP BY employee_id, week, day
SQL;


        $totalTimeSQL = <<<SQL
                            SELECT SUM(worked_time)                    AS worked_time,
                                   SUM(over_time)                      AS over_time,
                                   SUM(break_time)                     AS break_time,
                                   (SUM(worked_time) - SUM(over_time)) AS regular_time
                            FROM (
                                     SELECT SUM(worked_time)                                      AS worked_time,
                                            IF(SUM(worked_time) > 380, SUM(worked_time) - 380, 0) AS over_time,
                                            SUM(break_time)                                       AS break_time
                                     FROM ($dailySQL) AS dailies
                                     GROUP BY employee_id, week
                                 ) AS TOTAL
SQL;

        $bindings = [$primaryId, $fromDate->toDateTimeString(), $toDate->toDateTimeString()];

        $totals = (array) DB::selectOne($totalTimeSQL, $bindings);
        $summary = collect(DB::select("SELECT employee_id, day FROM ($dailySQL) AS list GROUP BY employee_id, day", $bindings));

        // @todo remove this when the global configuration is being setting time-zone up
        DB::statement('SET time_zone = :time_zone', ['time_zone' => 'UTC']);

        return TimeClockSummary::from(array_merge(
            $totals,
            [
                'dates' => $summary->pluck('day')->toArray(),
                'employees' => $summary->pluck('employee_id')->unique()->toArray(),
                'repair_orders' => []
            ]
        ));
    }

    /**
     * Checks if the employee has the clock ticking currently.
     *
     * @param  int  $employeeId
     * @return bool
     */
    public function isClockTicking(int $employeeId): bool
    {
        return TimeClock::where('employee_id', $employeeId)
            ->whereNull('punch_out')
            ->orderBy('id', 'desc')
            ->exists();
    }

    /**
     * Starts the clock for given employee
     *
     * @param  int  $employeeId
     * @return TimeClock
     * @throws LogicException when time clock is already ticking
     */
    public function markPunchIn(int $employeeId): TimeClock
    {
        $timeClock = TimeClock::where('employee_id', $employeeId)
            ->whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->orderBy('id', 'desc')
            ->first();

        if ($timeClock) {
            throw new LogicException('Time clock is already ticking.');
        }

        return TimeClock::punchIn($employeeId);
    }

    /**
     * Starts the clock for given employee
     *
     * @param  int  $employeeId
     * @return TimeClock
     * @throws LogicException when time clock is not ticking
     */
    public function markPunchOut(int $employeeId): TimeClock
    {
        /** @var TimeClock $timeClock */
        $timeClock = TimeClock::where('employee_id', $employeeId)
            ->whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->orderBy('id', 'desc')
            ->first();

        if ($timeClock === null) {
            throw new LogicException('Time clock is not ticking.');
        }

        return $timeClock->punchOut();
    }

    /**
     * @param  array  $filters
     *
     * @throws InvalidArgumentException when neither 'employee_id' and 'dealer_id' were not provided
     * @throws InvalidArgumentException when 'from_date' was not provided
     * @throws InvalidArgumentException when 'to_date' was not provided
     * @throws InvalidArgumentException when date range was too wide
     */
    private function minimumFiltersGuard(array $filters): void
    {
        if (empty($filters['employee_id']) && empty($filters['dealer_id'])) {
            throw new InvalidArgumentException("when 'employee_id' is not provided, the the 'dealer_id' is required.");
        }

        if (empty($filters['from_date'])) {
            throw new InvalidArgumentException("'from_date' is required filter.");
        }

        if (empty($filters['to_date'])) {
            throw new InvalidArgumentException("'to_date' is required filter.");
        }

        if (Date::parse($filters['from_date'])->diffInDays(Date::parse($filters['to_date'])) > 365) {
            throw new InvalidArgumentException('date range is too wide.');
        }
    }
}
