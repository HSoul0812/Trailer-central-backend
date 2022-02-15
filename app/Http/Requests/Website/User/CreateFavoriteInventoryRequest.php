<?php
namespace App\Http\Requests\Website\User;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateFavoriteInventoryRequest extends Request
{
    protected $rules = [
        'inventory_ids' => 'array|required',
        'inventory_ids.*' => 'integer'
    ];

    protected function getRules(): array
    {
        return [
            'inventory_ids' => 'array|required',
            'inventory_ids.*' => [
                'integer',
                Rule::exists('inventory', 'inventory_id')->where('dealer_id', $this->dealer_id)
            ]
        ];
    }
}
