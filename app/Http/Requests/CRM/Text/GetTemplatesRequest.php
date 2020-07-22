<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Get Templates Request
 * 
 * @author David A Conway Jr.
 */
class GetTemplatesRequest extends Request {
    
    protected $rules = [
        'name' => 'string',
        'per_page' => 'integer',
        'sort' => 'in:name,-name,created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
