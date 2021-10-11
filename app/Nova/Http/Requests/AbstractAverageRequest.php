<?php

declare(strict_types=1);

namespace App\Nova\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

abstract class AbstractAverageRequest extends Request implements InsightRequestInterface
{
    public function getPeriod(): string
    {
        return $this->input('period', self::PERIOD_PER_WEEK);
    }

    public function getFrom(): ?string
    {
        return $this->input('from', Date::now()->startOf('year')->format('Y-m-d'));
    }

    public function getTo(): ?string
    {
        return $this->input('to', Date::now()->format('Y-m-d'));
    }

    public function getSubset(): ?string
    {
        return $this->input('subset', '');
    }

    /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
    protected function getRules(): array
    {
        return [
            'period' => Rule::in([self::PERIOD_PER_DAY, self::PERIOD_PER_WEEK]), // by the moment
            'from'   => $this->validFromDate(),
            'to'     => $this->validToDate(),
        ];
    }

    private function validFromDate(): string
    {
        $toDate = $this->getTo();

        return $toDate ?
            sprintf('date_format:Y-m-d|before_or_equal:%s', $toDate) :
            'nullable|date_format:Y-m-d';
    }

    private function validToDate(): string
    {
        $fromDate = $this->getFrom();

        return $fromDate ?
            sprintf('date_format:Y-m-d|after_or_equal:%s', $fromDate) :
            'nullable|date_format:Y-m-d';
    }
}
