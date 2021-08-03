<?php

namespace App\Http\Requests\Dms\Pos;

use App\Http\Requests\Request;

class PostOpenRegisterRequest extends Request {
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'outlet_id' => 'integer|min:1|required|exists:crm_pos_outlet,id',
        'floating_amount' => 'numeric|min:0|required',
    ];
}
