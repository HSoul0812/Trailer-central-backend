<?php

declare(strict_types=1);

namespace App\Http\Requests\Integration;

use App\Http\Requests\WithDealerRequest;

/**
 * @property int $integration_id
 * @property boolean $active
 * @property string $settings
 * @property string $location_ids
 */
class UpdateDealerIntegrationRequest extends WithDealerRequest
{

    public function getRules(): array
    {
        return $this->rules + [
            'integration_id' => 'integer|min:1|required|exists:integration,integration_id',
            'active' => 'boolean|required',
            'settings' => 'string|required',
            'location_ids' => 'array|sometimes'
        ];
    }
}
