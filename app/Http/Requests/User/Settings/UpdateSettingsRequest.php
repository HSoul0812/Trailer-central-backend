<?php

namespace App\Http\Requests\User\Settings;

use App\Http\Requests\Request;

/**
 * Update Settings Request
 * 
 * @author David A Conway Jr.
 */
class UpdateSettingsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'settings' => 'required|array',
        'settings.setting' => 'string|max:255',
        'settings.value' => 'string|max:255',
    ];
    
}