<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class GetCategoryRequest extends Request {

    protected $rules = [
        'sort' => 'in:label,-label,title,-title',
        'entity_type_id' => 'integer',
    ];
}
