<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class SearchInventoryRequest extends Request
{
    protected $rules = [
        'per_page' => 'integer|min:1|max:100',
        'page' => ['integer', 'min:0']
    ];
}
