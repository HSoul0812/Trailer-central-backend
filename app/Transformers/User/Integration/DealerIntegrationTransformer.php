<?php

declare(strict_types=1);

namespace App\Transformers\User\Integration;

use App\Models\User\Integration\DealerIntegration;
use App\Services\User\DealerIntegrationServiceInterface;
use App\Traits\CompactHelper;
use League\Fractal\TransformerAbstract;

class DealerIntegrationTransformer extends TransformerAbstract
{
    /**
     * @var DealerIntegrationServiceInterface
     */
    private $service;

    public function __construct(DealerIntegrationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function transform(DealerIntegration $dealerIntegration): array
    {
        return [
            'id' => $dealerIntegration->integration_id,
            'identifier' => CompactHelper::shorten($dealerIntegration->integration_id), // for backward compatibility
            'name' => $dealerIntegration->integration->name,
            'description' => $dealerIntegration->integration->description,
            'listing_count' => 0, // not sure where this come from
            'domain' => $dealerIntegration->integration->domain,
            'create_account_url' => $dealerIntegration->integration->create_account_url,
            'created_at' => $dealerIntegration->created_at,
            'updated_at' => $dealerIntegration->updated_at,
            'last_run_at' => $dealerIntegration->last_run_at,
            'active' => (bool)$dealerIntegration->active,
            'settings' => $dealerIntegration->decodeSettingsWithValues(),
            'location_ids' => $dealerIntegration->decodeLocationIds(),
            'values' => $this->service->getValues($dealerIntegration->integration_id, $dealerIntegration->dealer_id)
        ];
    }
}
