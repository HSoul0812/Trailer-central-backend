<?php

namespace App\Http\Requests\Dms\PurchaseOrder;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class ShowPoReceiptRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}
