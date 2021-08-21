<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class GetCustomOverlaysRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|exists:App\Models\User\User,dealer_id'
    ];
}
