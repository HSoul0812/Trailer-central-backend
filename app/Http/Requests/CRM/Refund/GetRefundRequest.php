<?php

namespace App\Http\Requests\CRM\Refund;

use App\Http\Requests\Request;

/**
 * Class GetRefundRequest
 * @package App\Http\Requests\CRM\Refund
 */
class GetRefundRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'id' => 'required|integer',
    ];
}
