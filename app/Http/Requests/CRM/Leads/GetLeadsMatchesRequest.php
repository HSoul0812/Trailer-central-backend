<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class GetLeadsMatchesRequest
 *
 * @package App\Http\Requests\CRM\Leads
 */
class GetLeadsMatchesRequest extends Request
{
    /**
     * @return array
     */
    protected $rules = [
        'leads' => [
            'array',
            'min:1',
        ],
        'leads.*.type' => [
            'string',
            'in:email,phone,last_name',
        ],
        'leads.*.identifier' => [
            'string',
        ],
    ];
}
