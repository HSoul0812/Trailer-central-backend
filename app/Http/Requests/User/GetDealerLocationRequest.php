<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class GetDealerLocationRequest extends Request
{
    /** @var int */
    public $dealer_id;

    /** @var string */
    public $include = '';

    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'include' => 'in:fees'
    ];
}
