<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Update Template Request
 * 
 * @author David A Conway Jr.
 */
class UpdateTemplateRequest extends Request {
    
    protected $rules = [
        'id' => 'required|text_template_exists',
        'name' => 'string',
        'template' => 'string',
    ];
    
}