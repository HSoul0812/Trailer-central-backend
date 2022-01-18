<?php
namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

class ApproveCompletedOrderRequest extends Request
{
    protected $rules = [
        'textrail_order_id' => 'integer|min:1|required|exists:ecommerce_completed_orders,ecommerce_order_id',
    ];
}