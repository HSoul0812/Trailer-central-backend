<?php

namespace App\Http\Requests\Bulk\Blog;

use App\Http\Requests\Request;

/**
 *
 *
 * @author Eczek
 */
class CreateBulkUploadRequest extends Request {

    protected $rules = [
        'website_id' => 'required|integer',
        'dealer_id' => 'required|integer',
        'csv_file' => 'required|file',
        'token' => 'uuid'
    ];
}
