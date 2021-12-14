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
     * Should return the criteria used to filter by, e.g: if we're using a manufacturer aggregate, then we would like to
     * filter by 'Griffin' manufacturer, by the other hand, if we're using a category aggregate, then we would like to
     * filter by 'Boats', or 'Trucks'.
     */
    public function getSubset(): ?string;
}
