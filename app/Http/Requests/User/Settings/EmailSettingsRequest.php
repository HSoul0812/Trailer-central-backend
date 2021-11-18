<?php

namespace App\Http\Requests\User\Settings;

use App\Http\Requests\Request;

/**
 * Email Settings Request
 * 
 * @author David A Conway Jr.
 */
class EmailSettingsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        'sales_person_id' => 'integer|sales_person_valid'
    ];
    
}