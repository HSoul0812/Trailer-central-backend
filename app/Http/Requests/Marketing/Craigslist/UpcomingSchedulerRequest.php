<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get Upcoming Scheduler Request
 * 
 * @author David A Conway Jr.
 */
class UpcomingSchedulerRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer',
        'profile_id' => 'integer|valid_clapp_profile'
    ];
}
