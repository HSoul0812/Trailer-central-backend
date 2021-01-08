<?php

namespace App\Http\Requests\Website\Forms;

use App\Http\Requests\Request;

/**
 * Get Field Map Request for Form
 * 
 * @author David A Conway Jr.
 */
class GetFieldMapRequest extends Request {
    
    protected $rules = [
        'type' => 'valid_form_map_type'
    ];
    
    public function all($keys = null) {
        // Return Result
        return parent::all($keys);
    }
}
