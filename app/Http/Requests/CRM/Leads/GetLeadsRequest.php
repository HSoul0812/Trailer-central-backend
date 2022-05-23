<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

class GetLeadsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer',
        'lead_type' => 'array',
        'lead_type.*' => 'lead_type_valid',
        'lead_status' => 'array',
        'lead_status.*' => 'lead_status_valid',
        'sales_person_id' => 'sales_person_valid',
        'search_term' => 'string',
        'customer_name' => 'string',
        'is_archived' => 'in:0,1',
        'location' => 'exists:dealer_location,dealer_location_id',
        'date_from' => 'date',
        'date_to' => 'date',
        'sort' => 'in:id,-id,first_name,-first_name,last_name,-last_name,' .
                        'email,-email,created_at,-created_at,no_due_past_due_future_due,' .
                        'future_due_past_due_no_due,-most_recent,most_recent,status',
        'per_page' => 'integer',
        'page' => 'integer',
    ];
}
