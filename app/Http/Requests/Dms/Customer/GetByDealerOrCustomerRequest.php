<?php

namespace App\Http\Requests\Dms\Customer;

use App\Http\Requests\Request;

/**
 * Class GetByDealerOrCustomerRequest
 *
 * @package App\Http\Requests\Dms\Customer
 */
class GetByDealerOrCustomerRequest extends Request
{
    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'customer_id' => 'integer|min:1|nullable|exists:dms_customer,id',
            'fields' => 'array|required',
            'fields.*' => 'string|min:1',
        ];
    }
}
