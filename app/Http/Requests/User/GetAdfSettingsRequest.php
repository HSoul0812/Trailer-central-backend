<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class GetAdfSettingsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id'
    ];
}