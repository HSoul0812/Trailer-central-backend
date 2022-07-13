<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

/**
 * Update Interaction Request
 * 
 * @author David A Conway Jr.
 */
class UpdateInteractionRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'lead_id' => 'int',
        'lead_product_id' => 'nullable|int',
        'interaction_type' => 'interaction_type_valid',
        'interaction_notes' => 'interaction_note_valid:interaction_type',
        'interaction_time' => 'date_format:Y-m-d H:i:s',
        'is_closed' => 'in:0,1',
    ];
    
}