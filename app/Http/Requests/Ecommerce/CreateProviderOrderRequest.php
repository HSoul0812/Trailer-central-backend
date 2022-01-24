<?php
namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

class CreateProviderOrderRequest extends Request
{
    protected $rules = [
        'id' => 'integer|min:1|required|exists:ecommerce_completed_orders,id',
        'dealer_id' => 'integer|min:1|exists:dealer,dealer_id',
    ];
}