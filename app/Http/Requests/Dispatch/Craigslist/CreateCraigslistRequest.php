<?php

namespace App\Http\Requests\Dispatch\Craigslist;

use App\Http\Requests\Request;

/**
 * Create Craigslist Listing in DB
 * 
 * @package App\Http\Requests\Dispatch\Craigslist
 * @author David A Conway Jr.
 */
class CreateCraigslistRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|exists:dealer_clapp,dealer_id',
        'queue_id' => 'required|exists:clapp_session,queue_id',
        'session_id' => 'nullable|exists:clapp_session,session_id',
        'profile_id' => 'nullable|exists:clapp_profile,id',
        'inventory_id' => 'nullable|exists:inventory,inventory_id',
        'status' => 'required|string',
        'state' => 'nullable|string',
        'craigslist_id' => 'nullable|string',
        'view_url' => 'nullable|string',
        'manage_url' => 'nullable|string',
        'ip_addr' => 'nullable|string',
        'user_agent' => 'nullable|string'
    ];

}