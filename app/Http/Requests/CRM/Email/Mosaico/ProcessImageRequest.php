<?php

namespace App\Http\Requests\CRM\Email\Mosaico;

use App\Http\Requests\Request;

class ProcessImageRequest extends Request {

    protected $rules = [

        'dealer_id' => 'required|integer',
        'params' => 'required|string|regex:/^[0-9]{2,4}(,[0-9]{2,4})/',
        'src' => 'string|required_if:method,resize,cover',
        'method' => 'required|string|in:resize,cover,placeholder'
    ];
}