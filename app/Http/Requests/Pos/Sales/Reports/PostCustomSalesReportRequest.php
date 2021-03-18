<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos\Sales\Reports;

use App\Http\Requests\Request;

class PostCustomSalesReportRequest extends Request
{

    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'part_category' => 'nullable|integer|exists:part_categories,id',
        'major_unit_category' => 'nullable|integer|exists:inventory_category,inventory_category_id',
        'fee_type' => 'array',
        'fee_type.*' => 'nullable|string|exists:dealer_location_quote_fee,fee_type',
        'year' => 'nullable|integer|min:2000',
        'model' => 'nullable|string',
        'query' => 'nullable|string'
    ];
}
