<?php

namespace App\Http\Requests\Inventory\Attributes;

class IndexAttributesRequest
{
    protected array $rules = [
        'entity_type_id' => 'required|integer'
    ];
}
