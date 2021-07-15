<?php

declare(strict_types=1);

namespace App\Http\Requests\Bulk\Parts;

use App\Http\Requests\Request;

class CreateBulkReportRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'required|integer',
            'token' => 'uuid',
            'search_term' => 'string|nullable',
            'type_of_stock' => 'nullable|stock_type_valid',
            'from_date' => 'required_with:to_date|date_format:Y-m-d',
            'to_date' => 'required_with:from_date|date_format:Y-m-d|after_or_equal:from_date'
        ];
    }
}
