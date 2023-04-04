<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

class SaveEmailDraftRequest extends Request {
    
    protected $rules = [
        'lead_id' => 'required|integer',
        'dealer_id' => 'required|integer',
        'user_id' => 'required|integer',
        'sales_person_id' => 'integer',
        'subject' => 'required|string',
        'body' => 'required|string',
        'new_attachments' => 'array',
        'new_attachments.*' => 'file|max:20000',
        'existing_attachments' => 'array',
        'existing_attachments.*.filename' => 'string',
        'existing_attachments.*.original_filename' => 'string',
    ];
}