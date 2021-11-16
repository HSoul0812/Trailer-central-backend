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
        'sort' => 'in:no_due_past_due_future_due,created_at,future_due_past_due_no_due,-most_recent,most_recent,status',
        'per_page' => 'integer',
        'page' => 'integer',
    ];
}
