<?php

namespace App\Http\Requests\Bulk\Inventory;

use App\Http\Requests\Request;

class GetBulkUploadsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer'
    ];
}
