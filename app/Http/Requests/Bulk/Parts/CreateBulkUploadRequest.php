<?php

namespace App\Http\Requests\Bulk\Parts;

use App\Http\Requests\Request;

/**
 *
 *
 * @author Eczek
 */
class CreateBulkUploadRequest extends Request {

    protected $rules = [
        'dealer_id' => 'required|integer',
        'csv_file' => 'required|file',
        'token' => 'uuid'
    ];
}
