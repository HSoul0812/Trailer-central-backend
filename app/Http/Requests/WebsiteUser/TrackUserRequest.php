<?php

namespace App\Http\Requests\WebsiteUser;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class TrackUserRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'visitor_id' => 'required|string',
        'event' => 'required|string',
        'url' => 'required|string',
        'meta' => 'array',
    ];
}
