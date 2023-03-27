<?php

namespace App\Http\Requests\Feed\Factory;

use App\Http\Requests\Request;

/**
 * Class UploadFactoryFeedUnitRequest
 * @package App\Http\Requests\Feed\Factory
 */
class UploadFactoryFeedUnitRequest extends Request
{
    protected $rules = [
        'code' => 'string|required',
        'transactions' => 'array|required',
        'transactions.*.action' => 'string|required',
        'transactions.*.parameters' => 'required'
    ];
}
