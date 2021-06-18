<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;

class ValidateSalesPeopleRequest extends Request {
    protected $rules = [
        'type' => 'required|in:smtp,imap',
        'username' => 'required|email',
        'password' => 'required|confirmed',
        'security' => 'required|in:tls,ssl',
        'host' => 'required',
        'port' => 'required|integer',
    ];
}