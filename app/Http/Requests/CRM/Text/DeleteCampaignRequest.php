<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Delete Text Request
 *
 * @author David A Conway Jr.
 */
class DeleteTextRequest extends Request {
    
    protected $rules = [
        'id' => 'integer'
    ];
    
}
