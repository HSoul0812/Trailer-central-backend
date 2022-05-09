<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class CreateBinRequest extends Request
{
    protected $rules = [
        'location' => 'required|integer|location_belongs_to_dealer',
        'bin_name' => 'required'
    ];
}
