<?php

namespace App\Http\Requests\Dms\QzTray;

use App\Http\Requests\Request;

class GetQzSignatureRequest extends Request
{
    protected $rules = [
        'to_sign' => 'required|string',
    ];
}
