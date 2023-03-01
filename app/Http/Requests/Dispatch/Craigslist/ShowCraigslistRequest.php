<?php

namespace App\Http\Requests\Dispatch\Craigslist;

use App\Http\Requests\Request;

/**
 * Single Craigslist Dealer Request Status
 * 
 * @package App\Http\Requests\Dispatch\Craigslist
 * @author David A Conway Jr.
 */
class ShowCraigslistRequest extends Request {

    protected $rules = [
        'id' => 'required|valid_clapp_dealer',
        'include' => 'string|valid_include:accounts,profiles,cards,tunnels'
    ];

}