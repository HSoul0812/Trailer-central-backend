<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Models\Inventory\Inventory;
use Illuminate\Validation\Rule;

class FindByStockRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'stock' => Rule::exists(Inventory::getTableName())->where('dealer_id', $this->input('dealer_id')),
        ];
    }

    protected function messages(): array
    {
        return [
            'stock.exists' => "No inventory with the stock ':input'.",
        ];
    }
}
