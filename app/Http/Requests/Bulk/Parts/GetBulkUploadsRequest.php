<?php

namespace App\Http\Requests\Bulk\Parts;

use App\Http\Requests\Request;

/**
 * 
 *
 * @author Eczek
 */
class GetBulkUploadsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer'
    ];
    
}