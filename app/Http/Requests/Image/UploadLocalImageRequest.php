<?php

namespace App\Http\Requests\Image;

use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Request;

class UploadLocalImageRequest extends Request implements CreateRequestInterface
{
    protected array $rules = [
        'file' => 'required|image'
    ];
}
