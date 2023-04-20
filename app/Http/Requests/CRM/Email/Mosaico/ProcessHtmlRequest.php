<?php

namespace App\Http\Requests\CRM\Email\Mosaico;

use App\Http\Requests\Request;

class ProcessHtmlRequest extends Request {

    protected $rules = [

        'dealer_id' => 'required|integer',
        'user_id' => 'integer|required_if:action,email',
        'html' => 'required|string',
        'action' => 'required|string|in:download,email',
        'subject' => 'string|required_if:action,email',
        'rcpt' => 'string|required_if:action,email',
        'filename' => 'string|required_if:action,download'
    ];
}