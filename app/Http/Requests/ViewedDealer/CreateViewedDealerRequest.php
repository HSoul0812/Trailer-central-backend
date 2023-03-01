<?php

namespace App\Http\Requests\ViewedDealer;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class CreateViewedDealerRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'viewed_dealers' => 'array',
        'viewed_dealers.*.name' => 'string',
        'viewed_dealers.*.dealer_id' => 'int',
        'viewed_dealers.*.inventory_id' => 'integer',
    ];

    public function messages(): array
    {
        return [
            'viewed_dealers.required' => 'The viewed_dealers field is required.',
        ];
    }
}
