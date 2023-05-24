<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Create Template Request
 *
 * @author David A Conway Jr.
 */
class CreateTemplateRequest extends Request
{
    protected $rules = [
        'name' => 'string',
        'template' => 'string',
        'template_key' => 'required|string|unique:crm_email_template,template_key',
        'html' => 'string',
        'template_metadata' => 'string',
        'template_json' => 'string'
    ];
}
