<?php

namespace App\Http\Requests\Dms\Quickbooks;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetAccountsRequest extends Request {

    protected $rules = [
        'dealer_id' => 'integer',
    ];
    
}
