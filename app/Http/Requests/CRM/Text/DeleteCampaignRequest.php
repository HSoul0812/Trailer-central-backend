<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Delete Campaign Request
 *
 * @author David A Conway Jr.
 */
class DeleteCampaignRequest extends Request {
    
    protected $rules = [
        'id' => 'integer'
    ];
    
}
