<?php

declare(strict_types=1);

namespace App\Observers\User;

use App\Models\User\DealerLocation;
use App\Services\Quickbooks\DealerLocationServiceInterface;

/**
 * Implementation of default events for saving on DealerLocation
 */
class DealerLocationObserver
{
    /** @var DealerLocationServiceInterface */
    private $service;

    public function __construct(DealerLocationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function updated(DealerLocation $model): void
    {
        if ($this->hasChangedName($model)) {
            // @fixme: use a domain event like event(new DealerLocationNameUpdated($model->dealer_location_id));
            // given the mess on the tc-crm QBO integration, this could be handle reactively with domain events (to try to decouple it),
            // but right now it could be over engineering, so when the CRM and QBO integrations
            // starts to been migrated here, that's the better and maintainable way to approach it

            $this->service->update($model->dealer_location_id);
        }

        $this->service->reindexAndInvalidateCacheInventory($model->dealer_location_id);
    }

    private function hasChangedName(DealerLocation $model): bool
    {
        return $model->getOriginal('name') !== $model->name;
    }
}
