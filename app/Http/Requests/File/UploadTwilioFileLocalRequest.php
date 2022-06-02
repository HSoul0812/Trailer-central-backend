<?php

namespace App\Http\Requests\File;

use App\Http\Requests\Request;

/**
 * Class UploadTwilioFileLocalRequest
 * @package App\Http\Requests\File
 */
class UploadTwilioFileLocalRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer|exists:App\Models\User\User,dealer_id',
        'file' => 'required|file|mimes:jpeg,jpg,png,gif,tiff,mp4,oga,mp3,3gp,3g2,weba,mpeg,mp4,webm,3gp,3g2,csv,txt,ics,pdf,rtf'
    ];
}
