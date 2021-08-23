<?php

declare(strict_types=1);

namespace App\DTO\CRM\Users;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * @property-read float $regular_time
 * @property-read float $worked_time
 * @property-read float $over_time
 * @property-read float $break_time
 * @property-read array<string> $dates
 * @property-read array<int> $employees list of employee IDs
 * @property-read array<int> $repair_orders  list of repair orders IDs
 */
class TimeClockSummary implements DTO
{
    use WithFactory;
    use WithGetter;

    /** @var int */
    private $regular_time = 0;

    /** @var int */
    private $worked_time = 0;

    /** @var int */
    private $over_time = 0;

    /** @var int */
    private $break_time = 0;

    /** @var array<int> */
    private $dates = [];

    /** @var array<int> */
    private $employees = [];

    /** @var array<int> */
    private $repair_orders = [];

    public function asArray(): array
    {
        return [
            'regular_time' => (float) $this->regular_time,
            'worked_time' => (float) $this->worked_time,
            'over_time' => (float) $this->over_time,
            'break_time' => (float) $this->break_time,
            'dates' => $this->dates,
            'employees' => $this->employees,
            'repair_orders' => $this->repair_orders
        ];
    }
}
