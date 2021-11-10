<?php

namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

/**
 * @property array $data
 * @property string $id
 * @property string $object
 * @property string $type
 * @property string $dealer_id
 */
class CreateCompletedOrderRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|exists:dealer,dealer_id',
        'type' => 'required|in:checkout.session.completed',
        'object' => 'required|in:event',
        'data' => 'required',
        'id' => 'string',
    ];
}
