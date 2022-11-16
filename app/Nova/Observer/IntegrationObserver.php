<?php

namespace App\Nova\Observer;

use App\Models\Integration\Integration;

/**
 * Class IntegrationObserver
 * @package App\Nova\Observer
 */
class IntegrationObserver
{
    /**
     * @param Integration $integration
     * @throws \Exception
     */
    public function saving(Integration $integration): void
    {
        $castedIntegration = $integration->toArray();

        $integration->filters = serialize($castedIntegration["unserializeFilters"]);
        $integration->settings = serialize($castedIntegration["unserializeSettings"]);

        unset($integration->unserializeFilters);
        unset($integration->unserializeSettings);
    }
}
