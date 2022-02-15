<?php

namespace App\Http\Requests\Website\Forms;

use App\Http\Requests\Request;

/**
 * Types Field Map Request for Form
 * 
 * @author David A Conway Jr.
 */
class TypesFieldMapRequest extends Request {
    
    protected $rules = [];
    
    public function all($keys = null) {
        // Return Result
        return parent::all($keys);
    }
}
