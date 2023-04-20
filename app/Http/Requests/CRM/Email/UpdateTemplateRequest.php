<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Update Template Request
 * 
 * @author David A Conway Jr.
 */
class UpdateTemplateRequest extends Request {
    
    protected $rules = [
        'id' => 'required|email_template_exists',
        'name' => 'string',
        'template' => 'string',
        'html' => 'string',
        'template_metadata' => 'string',
        'template_json' => 'string'
    ];
    
}