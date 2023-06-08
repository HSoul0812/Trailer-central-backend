<?php

namespace App\Http\Requests\File;

use App\Http\Requests\Request;

/**
 * Class UploadImageRequest
 * @package App\Http\Requests\File\
 */
class UploadImageLocalRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'file' => 'required|mimes:jpeg,jpg,png,gif,webp'
    ];
}
