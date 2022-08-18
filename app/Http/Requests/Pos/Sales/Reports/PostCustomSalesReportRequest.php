<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos\Sales\Reports;

use App\Http\Requests\Request;
use App\Repositories\Pos\SalesReportRepository;
use Illuminate\Validation\Rule;

/**
 * Class PostCustomSalesReportRequest
 *
 * @package App\Http\Requests\Pos\Sales\Reports
 */
class PostCustomSalesReportRequest extends Request
{
    /**
     * @var string
     */
    private const DATE_FORMAT = 'date_format:Y-m-d';

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'part_category' => 'nullable|integer|exists:part_categories,id',
            'major_unit_category' => 'nullable|string|exists:inventory_category,legacy_category',
            'fee_type' => 'array',
            // 'fee_type.*' => 'nullable|string|exists:dealer_location_quote_fee,fee_type', // cant be performed due there is a hardcode fee
            'year' => 'nullable|integer|min:2000',
            'model' => 'nullable|string',
            'query' => 'nullable|string',
            'report_type' => [
                'nullable',
                'array',
                Rule::in(SalesReportRepository::CUSTOM_REPORT_TYPES),
            ],
            'from_date' => [
                'required',
                self::DATE_FORMAT,
                'before_or_equal:to_date',
            ],
            'to_date' => [
                'required',
                self::DATE_FORMAT,
                'after_or_equal:from_date',
            ],
        ];
    }
}
