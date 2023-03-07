<?php

namespace App\Http\Requests\Subscriptions;

use App\Http\Requests\Request;

/**
 * Class GetCustomerDealerIdRequest
 * @package App\Http\Requests\Subscriptions
 */
class GetCustomerByDealerIdRequest extends Request {

    /**
     * @var string[]
     */
    protected $rules = [
        'dealer_id' => 'integer|required'
    ];

}
