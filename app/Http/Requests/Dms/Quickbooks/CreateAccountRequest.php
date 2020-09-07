<?php

namespace App\Http\Requests\Dms\Quickbooks;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class CreateAccountRequest extends Request {
    
    protected $rules = [
        'name' => 'string|required',
        'dealer_id' => 'integer|required',
        'type' => 'string|required',
        'sub_type' => 'string|required',
        'sub_account' => 'boolean',
        'parent_id' => 'integer',
        'current_balance' => 'numeric',
        'current_balance_with_subaccounts' => 'numeric',
    ];
    
}
