<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class GetUniqueFullNamesRequest
 * @package App\Http\Requests\CRM\Leads
 */
class GetUniqueFullNamesRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'per_page' => 'required|integer',
        'is_archived' => 'boolean',
        'search_term' => 'nullable|string|max:256',
    ];
}
