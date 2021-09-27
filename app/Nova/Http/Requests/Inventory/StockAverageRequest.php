<?php

declare(strict_types=1);

namespace App\Nova\Http\Requests\Inventory;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

class StockAverageRequest extends Request implements StockAverageRequestInterface
{
    public function getPeriod(): string
    {
        return $this->input('period', self::PERIOD_PER_WEEK);
    }

    public function getFrom(): ?string
    {
        return $this->input('from', Date::createFromFormat('Y-m-d', '2021-01-01')->format('Y-m-d'));
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
