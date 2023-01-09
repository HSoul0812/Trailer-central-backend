<?php

namespace App\Services\Inventory;

class InventoryUpdateSource implements InventoryUpdateSourceInterface
{
    public function integrations(): bool
    {
        if ($clientId = request()->header('x-client-id')) {
            return $clientId === config('integrations.inventory_cache_auth.credentials.access_token');
        }
        return false;
    }
}
