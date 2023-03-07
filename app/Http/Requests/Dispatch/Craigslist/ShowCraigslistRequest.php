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
        'dealer_id' => 'required|exists:dealer_clapp,dealer_id',
        'include.*' => 'in:accounts,profiles,cards,tunnels,inventories,updates'
    ];

}