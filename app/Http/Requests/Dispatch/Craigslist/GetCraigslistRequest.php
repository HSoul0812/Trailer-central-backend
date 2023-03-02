<?php

namespace App\Http\Requests\Dispatch\Craigslist;

use App\Http\Requests\Request;

/**
 * Get Craigslist Request Status
 * 
 * @package App\Http\Requests\Dispatch\Craigslist
 * @author David A Conway Jr.
 */
class GetCraigslistRequest extends Request {

    protected $rules = [
        'per_page' => 'integer',
        'page' => 'integer',
        'type' => 'string|in:posted,profiles,scheduled,upcoming,now'
    ];

}