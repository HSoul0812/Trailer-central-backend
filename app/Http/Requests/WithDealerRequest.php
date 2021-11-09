<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * @property int $dealer_id
 */
abstract class WithDealerRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|exists:App\Models\User\User,dealer_id'
    ];
}
