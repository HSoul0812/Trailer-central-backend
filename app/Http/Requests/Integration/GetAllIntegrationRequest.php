<?php

declare(strict_types=1);

namespace App\Http\Requests\Integration;

use App\Http\Requests\WithDealerRequest;

/**
 * @property int $integration_id
 * @property boolean $integrated
 */
class GetAllIntegrationRequest extends WithDealerRequest
{
    public function getRules(): array
    {
        return array_merge($this->rules, [
            'integrated' => 'boolean',
        ]);
    }
}
