<?php

namespace App\Http\Requests\Website\PaymentCalculator;

use App\Http\Requests\Request;

class CreateSettingsRequest extends Request {

    protected $rules = [
        'entity_type_id' => 'required|integer',
        'months' => 'required|integer|in:0,12,24,36,48,60,72,84,96,108,120,132,144,156,168,180,192,204,216,228,240', // from 1 to 20 years
        'apr' => 'required|numeric|min:0|max:100',
        'down' => 'required|numeric|min:0',
        'inventory_condition' => 'in:used,new',
        'operator' => 'required|in:less_than,over',
        'inventory_price' => 'required|numeric|min:0',
        'financing' => 'in:financing,no_financing'
    ];
}
