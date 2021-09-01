<?php

declare(strict_types=1);

namespace App\DTO\CRM\Users;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @property-read LengthAwarePaginator $log
 * @property-read TimeClockSummary $summary
 */
class TimeClockQueryResult implements DTO
{
    use WithFactory;
    use WithGetter;

    /** @var LengthAwarePaginator */
    private $log;

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
            'log' => $this->log
        ];
    }
}
