<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class IndexInventoryRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'type_id' => 'required|integer',
        'country' => ["regex:/^(us|ca)$/i"],
        'sort' => ["regex:/^(\+|\-){0,1}(distance|createdAt|price|numFeatures)$/"]
    ];
}
