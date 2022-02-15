<?php

namespace App\Http\Requests\Feed\Factory;

use App\Http\Requests\Request;

/**
 * Class GetShowroomRequest
 * @package App\Http\Requests\Feed\Factory
 */
class GetShowroomRequest extends Request
{
    protected $rules = [
        'id' => 'integer|required|exists:App\Models\Showroom\Showroom,id',
    ];
}
