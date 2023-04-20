<?php

namespace App\Http\Requests\CRM\Email\Mosaico;

use App\Http\Requests\Request;

class UploadImagesRequest extends Request {

    protected $rules = [

        'dealer_id' => 'required|integer',
        'files' => 'array|min:1',
        'files.*' => 'file|mimes:jpg,jpeg,png,bmp'
    ];
}