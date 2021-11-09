<?php
namespace App\Http\Requests\Website\Configuration\Showroom;

use App\Http\Requests\Request;

class SaveShowroomConfigRequest extends Request
{
    protected $rules = [
        'websiteId' => 'integer|required'
    ];
}