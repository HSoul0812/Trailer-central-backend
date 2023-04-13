<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class GetSettingsRequest extends Request {

    protected $rules = [
        'user_id' => 'required|integer'
    ];
}
