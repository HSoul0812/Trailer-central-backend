<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

/**
 * Get Interactions Request
 * 
 * @author David A Conway Jr.
 */
class GetInteractionsRequest extends Request {
    
    protected $rules = [
        'lead_id' => 'required|int',
        'include_texts' => 'boolean',
        'per_page' => 'integer',
        'sort' => 'in:created_at,-created_at',
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
