<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get Recent Scheduler Request
 * 
 * @author David A Conway Jr.
 */
class RecentSchedulerRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer',
        'profile_id' => 'integer|valid_clapp_profile',
        'slot_id' => 'integer'
    ];
}
