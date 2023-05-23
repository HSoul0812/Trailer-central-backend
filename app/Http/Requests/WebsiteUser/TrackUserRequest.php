<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class TrackUserRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'visitor_id' => 'required|string',
        'event' => 'string',
        'url' => 'required|string',
        'page_name' => 'nullable|string',
        'meta' => 'array',
    ];
}
