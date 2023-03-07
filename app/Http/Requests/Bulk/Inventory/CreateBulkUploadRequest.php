<?php

namespace App\Http\Requests\Bulk\Inventory;

use App\Http\Requests\Request;

class CreateBulkUploadRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'title' => 'required|string',
        'csv_file' => 'required|file',
        'token' => 'uuid'
    ];
}
