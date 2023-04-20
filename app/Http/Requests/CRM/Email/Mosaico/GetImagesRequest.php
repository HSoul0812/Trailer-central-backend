<?php

namespace App\Http\Requests\CRM\Email\Mosaico;

use App\Http\Requests\Request;

class GetImagesRequest extends Request {

    protected $rules = [

        'dealer_id' => 'required|integer'
    ];
}