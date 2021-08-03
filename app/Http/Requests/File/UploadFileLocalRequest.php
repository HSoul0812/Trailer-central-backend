<?php

namespace App\Http\Requests\File;

use App\Http\Requests\Request;

/**
 * Class UploadFileRequest
 * @package App\Http\Requests\File
 */
class UploadFileLocalRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'file' => 'required|file'
    ];
}
