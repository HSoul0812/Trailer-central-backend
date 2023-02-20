<?php

namespace App\Http\Requests\Inventory\Files;

use App\Http\Requests\Request;

/**
 * Class CreateFileRequest
 * @package App\Http\Requests\Inventory\Files
 */
class CreateFileRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:App\Models\User\User,dealer_id',
        'inventory_id' => 'required|inventory_valid',
        'url' => 'string|required',
        'title' => 'string'
    ];
}
