<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Get Texts Request
 * 
 * @author David A Conway Jr.
 */
class GetTextsRequest extends Request {
    
    protected $rules = [
        'from_number' => 'regex:/(0-9)?[0-9]{10}/',
        'to_number' => 'regex:/(0-9)?[0-9]{10}/',
        'per_page' => 'integer',
        'sort' => 'in:from_number,-from_number,to_number,-to_number,date_sent,-date_sent',
        'id' => 'array',
        'id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
