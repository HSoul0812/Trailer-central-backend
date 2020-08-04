<?php

namespace App\Http\Requests\Website\Mail;

use App\Http\Requests\Request;

/**
 * Class AutoRespondRequest
 * @package App\Http\Requests\Website\Mail
 */
class AutoRespondRequest extends Request
{
    protected $rules = [
        'leadId' => 'required|integer|exists:website_lead,identifier'
    ];
}
