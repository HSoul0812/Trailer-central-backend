<?php

namespace App\Http\Requests\Website\Forms;

use App\Http\Requests\Request;

/**
 * Get Field Map Request for Form By Type
 * 
 * @author David A Conway Jr.
 */
class TypeFieldMapRequest extends Request {
    
    protected $rules = [
        'type' => 'required|valid_form_map_type'
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
