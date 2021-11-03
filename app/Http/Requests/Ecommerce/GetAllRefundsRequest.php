<?php

declare(strict_types=1);

namespace App\Http\Requests\Ecommerce;

use App\Http\Requests\Request;

/**
 * @property int $dealer_id
 */
class GetAllRefundsRequest extends Request
{
    public function getRules(): array
    {
        return [
            'dealer_id' => 'required|integer|exists:dealer,dealer_id',
        ];
    }
}
