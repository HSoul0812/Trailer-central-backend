<?php

declare(strict_types=1);

namespace App\Transformers\Integration;

use App\Models\Integration\Integration;
use App\Traits\CompactHelper;
use League\Fractal\TransformerAbstract;

class IntegrationTransformer extends TransformerAbstract
{

    public function transform(Integration $integration): array
    {
        return [
            'id' => $integration->integration_id,
            'code' => $integration->code,
            'identifier' => CompactHelper::shorten($integration->integration_id), // for backward compatibility
            'module_name' => $integration->module_name,
            'module_status' => $integration->module_status,
            'name' => $integration->name,
            'description' => $integration->description,
            'domain' => $integration->domain,
            'create_account_url' => $integration->create_account_url,
            'active' => (bool)$integration->active,
            'filters' => $integration->decodeFilters(),
            'frequency' => $integration->frequency,
            'last_run_at' => $integration->last_updated_at,
            'settings' => $integration->decodeSettings(),
            'include_sold' => $integration->include_sold,
            'send_email' => $integration->send_email,
            'uses_staging' => $integration->uses_staging,
            'show_for_integrated' => $integration->show_for_integrated
        ];
    }
}
