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
        'filter.invoice:unit_sale_id.eq' => 'integer|exists:App\Models\CRM\Dms\UnitSale,id',
        'created_at' => 'date_format:Y-m-d H:i:s',
        'register_id' => 'integer',
        'filter.invoice:customer_id.eq' => 'integer',
    ];
}
