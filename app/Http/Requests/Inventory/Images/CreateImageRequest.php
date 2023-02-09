<?php

namespace App\Http\Requests\Inventory\Images;

use App\Http\Requests\Request;

/**
 * Class CreateImageRequest
 * @package App\Http\Requests\Inventory\Images
 */
class CreateImageRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:App\Models\User\User,dealer_id',
        'inventory_id' => 'required|inventory_valid',
        'url' => 'string|required',
        'position' => 'integer|nullable',
        'primary' => 'checkbox|nullable',
        'is_stock' => 'checkbox|nullable',
        'is_secondary' => 'checkbox|nullable',
    ];
}
