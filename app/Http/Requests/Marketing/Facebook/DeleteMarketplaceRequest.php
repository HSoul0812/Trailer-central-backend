<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Delete Marketplace Request
 *
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class DeleteMarketplaceRequest extends Request {

    protected $rules = [
        'id' => 'required|integer'
    ];

}