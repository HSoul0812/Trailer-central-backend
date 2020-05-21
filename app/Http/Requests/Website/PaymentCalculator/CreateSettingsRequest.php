<?php

namespace App\Http\Requests\Website\PaymentCalculator;

use App\Http\Requests\Request;

class CreateSettingsRequest extends Request {
    
    protected $rules = [
        'entity_type_id' => 'required|integer',
        'months' => 'required|integer|between:0,12',
        'apr' => 'required|numeric',
        'down' => 'required|numeric',
        'inventory_condition' => 'in:used,new',
        'operator' => 'required|in:less_than,over',
        'inventory_price' => 'required|numeric'
    ];
    
}
