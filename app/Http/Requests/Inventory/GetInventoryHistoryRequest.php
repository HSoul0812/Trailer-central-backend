<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class GetInventoryHistoryRequest extends Request
{
    protected $rules = [
        'per_page' => 'integer|min:1|max:2000', // Sets 2000 for max to prevent memory leaks
        'sort' => 'in:created_at,-created_at,type,-type,subtype,-subtype,customer_name,-customer_name',
        'search_term' => 'string',
        'inventory_id' => 'required|integer',
        'customer_id' => 'integer'
    ];
}
