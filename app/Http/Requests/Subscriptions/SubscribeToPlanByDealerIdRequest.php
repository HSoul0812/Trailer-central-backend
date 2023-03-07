<?php

namespace App\Http\Requests\Subscriptions;

use App\Http\Requests\Request;

/**
 * Class SubscribeToPlanByDealerIdRequest
 * @package App\Http\Requests\Subscriptions
 */
class SubscribeToPlanByDealerIdRequest extends Request {

    /**
     * @var string[]
     */
    protected $rules = [
        'dealer_id' => 'integer|required',
        'plan' => 'string|required',
    ];

}
