<?php

namespace App\Http\Requests\Integration;

use App\Http\Requests\Request;

/**
 * Class UpdateCollectorRequest
 * @package App\Http\Requests\Integration
 */
class UpdateCollectorRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|exists:dealer,dealer_id',
        'id' => 'integer|min:1|collector_valid',
        'override_all' => 'in:0,1,2',
        'override_images' => 'in:0,1,2',
        'override_video' => 'in:0,1,2',
        'override_prices' => 'in:0,1,2',
        'override_attributes' => 'in:0,1,2',
        'override_descriptions' => 'in:0,1,2',
    ];
}
