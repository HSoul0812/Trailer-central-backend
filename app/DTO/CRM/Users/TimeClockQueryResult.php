<?php

declare(strict_types=1);

namespace App\DTO\CRM\Users;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @property-read Collection $timelog
 * @property-read Collection $worklog
 * @property-read TimeClockSummary $summary
 */
class TimeClockQueryResult implements DTO
{
    use WithFactory;
    use WithGetter;

    /** @var Collection */
    private $timelog;

    /** @var Collection */
    private $worklog;

    /** @var TimeClockSummary */
    private $summary;

    public static function result(array $properties): self
    {
        return self::from($properties);
    }

    public function asArray(): array
    {
        return [
            'summary' => $this->summary,
            'timelog' => $this->timelog,
            'worklog' => $this->worklog,
        ];
    }
}
