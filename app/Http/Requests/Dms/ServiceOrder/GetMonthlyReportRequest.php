<?php

namespace App\Http\Requests\Dms\ServiceOrder;

use App\Http\Requests\Request;

/**
 * Class GetMonthlyReportRequest
 * @package App\Http\Requests\Dms\ServiceOrder
 */
class GetMonthlyReportRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:dealer,dealer_id',
        'per_page' => 'integer|min:1|max:2000', // Sets 2000 for max to prevent memory leaks
        'search_term' => 'string',
        'sort' => 'in:month_name,-month_name,type,-type,unit_price,-unit_price'
    ];
}
