<?php

declare(strict_types=1);

namespace App\Nova\Http\Requests;

interface InsightRequestInterface extends CardRequestInterface
{
    public const PERIOD_PER_DAY = 'per_day';
    public const PERIOD_PER_WEEK = 'per_week';
    public const PERIOD_PER_MONTH = 'per_month';
    public const PERIOD_PER_YEAR = 'per_year';

    public function getPeriod(): string;

    public function getFrom(): ?string;

    public function getTo(): ?string;

    public function getAggregateValue(): ?string;
}
