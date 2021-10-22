<?php
namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

class GetCompletedOrderRequest extends Request
{
    protected $rules = [
        'status' => 'in:dropshipped,abandoned,unfulfilled,pending,fulfilled,manual',
        'search_term' => 'string',
        'customer_name' => 'string',
        'date_from' => 'date_format:Y-m-d H:i:s',
        'date_to' => 'date_format:Y-m-d H:i:s',
        'sort' => 'in:status,customer_email,created_at'
    ];
}