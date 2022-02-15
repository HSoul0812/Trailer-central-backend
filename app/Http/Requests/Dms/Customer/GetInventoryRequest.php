<?php

declare(strict_types=1);

namespace App\Http\Requests\Dms\Customer;

use App\Http\Requests\Request;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use Illuminate\Validation\Rule;

class GetInventoryRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:2000', // Sets 2000 for max to prevent memory leaks
            'sort' => 'in:title,-title,vin,-vin,manufacturer,-manufacturer,status,-status',
            'search_term' => 'string',
            'dealer_id' => 'integer|min:1|required',
            'customer_id' => 'array|required',
            'customer_id.*' => 'integer|min:0',
            'customer_condition' => Rule::in(InventoryRepositoryInterface::TENANCY_CONDITION),
        ];
    }
}
