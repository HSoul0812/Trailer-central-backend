<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class CreateCycleCountRequest extends Request {
    
    protected $rules = [
        'bin_id' => 'integer',
        'dealer_id' => 'integer',
        'is_completed' => 'boolean',
        'is_balanced' => 'boolean',
        'parts' => 'required|array',
        'parts.*.part_id' => 'required|integer|part_exists',
        'parts.*.count_on_hand' => 'required|integer',
        'parts.*.starting_qty' => 'required|integer',
    ];
    
}
