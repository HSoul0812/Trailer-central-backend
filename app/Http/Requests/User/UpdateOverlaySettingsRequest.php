<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class UpdateOverlaySettingsRequest extends Request 
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'overlay_logo' => 'mimes:png,jpg,jpeg',
        'overlay_enabled' => 'integer',
        'overlay_default' => 'integer',
        'overlay_logo_position' => 'string',
        'overlay_logo_width' => 'regex:/^[0-9]+%?$/',
        'overlay_logo_height' => 'regex:/^[0-9]+%?$/',
        'overlay_upper' => 'string',
        'overlay_upper_bg' => 'string',
        'overlay_upper_alpha' => 'integer',
        'overlay_upper_text' => 'string',
        'overlay_upper_size' => 'integer',
        'overlay_upper_margin' => 'integer',
        'overlay_lower' => 'string',
        'overlay_lower_bg' => 'string',
        'overlay_lower_alpha' => 'integer',
        'overlay_lower_text' => 'string',
        'overlay_lower_size' => 'integer',
        'overlay_lower_margin' => 'integer'
    ];
    
}
