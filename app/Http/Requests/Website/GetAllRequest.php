<?php

namespace App\Http\Requests\Website;

use App\Http\Requests\Request;

/**
 * Class GetAllRequest
 * @package App\Http\Requests\Website
 */
class GetAllRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'type' => 'string|in:classified,custom,minisite,website',
        'per_page' => 'required|int',
    ];
}
