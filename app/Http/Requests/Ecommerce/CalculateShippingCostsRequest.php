<?php
namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

class CalculateShippingCostsRequest extends Request
{
    protected $rules = [
        'customer_details' => 'array',
        'shipping_details' => 'required|array',
        'items' => 'required|array'
    ];
}