<?php

namespace App\Http\Requests\Website\PaymentCalculator;

use App\Http\Requests\Request;

class GetSettingsRequest extends Request {
    
    protected $rules = [
        'inventory_condition' => 'in:used,new',
        'inventory_price' => 'numeric',
        'entity_type_id' => 'integer',
        'financing' => 'in:financing,no_financing'
    ];
    
}
