<?php
namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

class CreateCompletedOrderRequest extends Request
{
    protected $rules = [
        'type' => 'required|in:checkout.session.completed',
        'object' => 'required|in:event',
        'data' => 'required',
    ];
}