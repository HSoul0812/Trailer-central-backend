<?php

namespace App\Http\Requests\Dms;

use App\Http\Requests\Request;

/**
 * Class DeleteCustomerRequest
 * @package App\Http\Requests\Dms
 */
class DeleteCustomerRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required|exists:dealer,dealer_id',
        'id' => 'integer|required'
    ];

}
