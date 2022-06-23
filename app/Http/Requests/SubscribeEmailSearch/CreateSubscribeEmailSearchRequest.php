<?php

declare(strict_types=1);

namespace App\Http\Requests\SubscribeEmailSearch;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class CreateSubscribeEmailSearchRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'email' => 'required|email',
        'url' => 'required|url',
    ];
}
