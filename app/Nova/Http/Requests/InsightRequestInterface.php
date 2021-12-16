<?php

declare(strict_types=1);

namespace App\Nova\Http\Requests;

interface InsightRequestInterface extends CardRequestInterface
{
    public const PERIOD_PER_DAY = 'per_day';
    public const PERIOD_PER_WEEK = 'per_week';
    public const PERIOD_PER_MONTH = 'per_month';
    public const PERIOD_PER_QUARTER = 'per_quarter';
    public const PERIOD_PER_YEAR = 'per_year';

    public function getPeriod(): string;

    public function getFrom(): ?string;

    public function getTo(): ?string;

    /**
     * Should return the criteria used to filter by, e.g:
     *  - by manufacturer
     *  - by category
     *  - by group.
     **/
    public function getSubset(): array|string|null;
}
