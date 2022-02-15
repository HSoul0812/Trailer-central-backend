<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Delete Template Request
 *
 * @author David A Conway Jr.
 */
class DeleteTemplateRequest extends Request {
    
    protected $rules = [
        'id' => 'integer'
    ];
    
}
