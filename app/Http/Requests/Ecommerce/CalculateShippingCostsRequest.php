<?php
namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

class CalculateShippingCostsRequest extends Request
{
    protected $rules = [
        'customer_details' => 'required|array',
        'shipping_details' => 'required|array',
        'items' => 'required|array'
    ];
}