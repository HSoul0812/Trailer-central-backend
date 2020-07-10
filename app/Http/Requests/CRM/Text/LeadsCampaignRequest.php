<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Get Blast Leads Request
 * 
 * @author David A Conway Jr.
 */
class LeadsBlastRequest extends Request {
    
    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:date_submitted,-date_submitted'
    ];
    
    public function all($keys = null) {
        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
