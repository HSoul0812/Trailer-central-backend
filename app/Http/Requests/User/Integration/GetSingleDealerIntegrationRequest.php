<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Integration;

use App\Http\Requests\WithDealerRequest;

/**
 * @property int $integration_id
 */
class GetSingleDealerIntegrationRequest extends WithDealerRequest
{
    public function getRules(): array
    {
        return $this->rules + ['integration_id' => 'integer|min:1|required|exists:integration,integration_id'];
    }
}