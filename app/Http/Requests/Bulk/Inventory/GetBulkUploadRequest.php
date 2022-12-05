<?php

namespace App\Http\Requests\Bulk\Inventory;

use App\Http\Requests\Request;

class GetBulkUploadRequest extends Request
{
    protected $rules = [
        'id' => 'required|integer'
    ];
}
