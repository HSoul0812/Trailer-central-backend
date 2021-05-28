<?php

namespace App\Http\Requests\Dms\Pos;

use App\Http\Requests\Request;

class GetRegistersRequest extends Request {

    protected $rules = [
        'dealer_id' => 'integer',
    ];

}
