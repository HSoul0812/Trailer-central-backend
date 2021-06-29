<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class ValidateSalesPeopleRequest extends Request {
    protected $rules = [
        'type' => 'required|in:smtp,imap',
        'username' => 'nullable|email',
        'password' => 'nullable|string',
        'security' => 'required|in:tls,ssl',
        'host' => 'nullable|string',
        'port' => 'nullable|integer',
    ];
}