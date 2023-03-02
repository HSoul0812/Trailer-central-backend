<?php

namespace App\Http\Requests\Dispatch\Craigslist;

use App\Http\Requests\Request;

/**
 * Login Craigslist Request
 * 
 * @package App\Http\Requests\Dispatch\Craigslist
 * @author David A Conway Jr.
 */
class LoginCraigslistRequest extends Request {

    protected $rules = [
        'ip_address' => 'required|ip',
        'client_uuid' => [
            'required',
            'regex:/(sch|cr|cl)\d{10,}/'
        ],
        'version' => [
            'required',
            'regex:/\d+[.]\d+[.]\d+/'
        ]
    ];

}