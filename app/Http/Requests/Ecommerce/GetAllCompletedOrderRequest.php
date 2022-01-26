<?php
namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

/**
 * @property int dealer_id
 */
class GetAllCompletedOrderRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'status' => 'in:dropshipped,abandoned,unfulfilled,pending,fulfilled,manual',
        'search_term' => 'string',
        'customer_name' => 'string',
        'date_from' => 'date_format:Y-m-d H:i:s',
        'date_to' => 'date_format:Y-m-d H:i:s',
        'sort' => 'in:status,customer_email,created_at'
    ];
}
