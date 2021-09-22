<?php

declare(strict_types=1);

namespace App\Nova\Http\Requests\Inventory;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class StockAverageRequest extends Request implements StockAverageRequestInterface
{
    public function getPeriod(): string
    {
        return $this->input('period', self::PERIOD_PER_DAY);
    }

    public function getFrom(): ?string
    {
        return $this->input('from');
    }

    public function getTo(): ?string
    {
        return $this->input('to');
    }

    public function getAggregateValue(): ?string
    {
        return $this->input('aggregate_value', 'Kz'); // dummy value by the moment
    }

    /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
    protected function getRules(): array
    {
        return [
            'period' => Rule::in([self::PERIOD_PER_DAY, self::PERIOD_PER_WEEK]), // by the moment
            'from'   => 'nullable|date_format:Y-m-d',
            'to'     => $this->validToDate(),
        ];
    }

    private function validToDate(): string
    {
        $formDate = $this->getFrom();

        return $formDate ?
            sprintf('required_with:from|date_format:Y-m-d|after_or_equal:%s', $formDate) :
            'required_with:from|date_format:Y-m-d';
    }
}
