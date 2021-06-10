<?php


namespace App\Http\Requests\CRM\Refund;

use App\Http\Requests\Request;

/**
 * Class GetRefundsRequest
 * @package App\Http\Requests\CRM\Payment\
 */
class GetRefundsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required_without_all:filter.dealer_id.eq|integer|exists:App\Models\User\User,dealer_id',
        'filter.dealer_id.eq' => 'required_without_all:dealer_id|integer|exists:App\Models\User\User,dealer_id',
    ];
}
