<?php

namespace App\Http\Requests\User\Settings;

use App\Http\Requests\Request;

/**
 * Get Settings Request
 * 
 * @author David A Conway Jr.
 */
class GetSettingsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'setting' => 'setting|max:255'
    ];
    
}