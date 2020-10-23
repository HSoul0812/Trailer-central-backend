<?php

namespace App\Http\Requests\Website;

use App\Http\Requests\Request;

/**
 * Class EnableProxiedDomainSslRequest
 * @package App\Http\Requests\Website
 */
class EnableProxiedDomainSslRequest extends Request
{
    protected $rules = [
        'website_id' => 'required|integer|website_valid'
    ];
}
