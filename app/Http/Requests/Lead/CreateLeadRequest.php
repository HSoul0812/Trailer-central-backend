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
      'email_address' => 'email',
      'phone_number' => 'regex:/(0-9)?[0-9]{10}/',
      'comments' => 'string',
    ];
}