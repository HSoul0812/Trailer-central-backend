<?php

declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class CreateLeadRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'dealer_location_id' => 'required',
        'lead_types' => 'required|array',
        'inventory' => 'required|array',
        'email_address' => 'email',
        'phone_number' => 'regex:/(0-9)?[0-9]{10}/',
        'comments' => 'string',
        'captcha' => 'required|string',
    ];
}
