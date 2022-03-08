<?php

namespace App\Http\Requests\CRM\Leads;

use App\Http\Requests\Request;

/**
 * Class FirstLeadRequest
 * @package App\Http\Requests\CRM\Leads
 */
class FirstLeadRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|required|exists:dealer,dealer_id',
    ];
}
