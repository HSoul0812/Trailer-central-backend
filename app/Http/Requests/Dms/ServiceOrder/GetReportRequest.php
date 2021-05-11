<?php

declare(strict_types=1);

namespace App\Http\Requests\Dms\ServiceOrder;

use App\Http\Requests\Request;

abstract class GetReportRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:dealer,dealer_id',
        'per_page' => 'integer|min:1|max:2000', // Sets 2000 for max to prevent memory leaks
        'search_term' => 'string'
    ];

    public function getDealerId(): ?int
    {
        return $this->input('dealer_id');
    }
}
