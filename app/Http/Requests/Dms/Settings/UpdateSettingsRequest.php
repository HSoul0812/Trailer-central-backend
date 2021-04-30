<?php

namespace App\Http\Requests\Dms\Settings;

use App\Http\Requests\Request;

class UpdateSettingsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'integer|required|exists:dealer,dealer_id',
        'meta' => 'array' // mixed, hard to validate
    ];
}
