<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *
 *
 * @author Eczek
 */
class ShowPartRequest extends Request {

    protected $rules = [
        'id' => 'required|integer'
    ];
}
