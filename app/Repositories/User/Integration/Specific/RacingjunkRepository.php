<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration\Specific;

use App\Models\Inventory\Inventory;

class RacingjunkRepository implements SpecificIntegrationRepositoryInterface
{
    use WithUsedSlotsGetter;

    public const SHOWN_ON_RACINGJUNK = 1;

    /**
     * @var Inventory
     */
    private $model;

    public function __construct(Inventory $model)
    {
        $this->model = $model;
    }

    protected function getUsedSlotsByDealerId(?int $dealerId): int
    {
        $query = $this->model::query();

        if ($dealerId) {
            $query->where('dealer_id', $dealerId);
        }

        $query->where('show_on_racingjunk', self::SHOWN_ON_RACINGJUNK)
            ->whereNotIn('status', Inventory::UNAVAILABLE_STATUSES);

        return $query->count('inventory_id');
    }
}