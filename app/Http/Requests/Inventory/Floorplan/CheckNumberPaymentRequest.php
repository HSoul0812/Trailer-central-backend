<?php

namespace App\Http\Requests\Inventory\Floorplan;

use App\Http\Requests\Request;

/**
 * Class CheckNumberPaymentRequest
 *
 * @package App\Http\Requests\Inventory\Floorplan
 */
class CheckNumberPaymentRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:App\Models\User\User,dealer_id',
        'checkNumber' => [
            'required',
            'string',
        ],
    ];
}
