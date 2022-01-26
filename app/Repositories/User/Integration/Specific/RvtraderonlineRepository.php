<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration\Specific;

use App\Models\Inventory\Inventory;

class RvtraderonlineRepository implements SpecificIntegrationRepositoryInterface
{
    use WithUsedSlotsGetter;

    public const SHOWN_ON_RVTRADER = 1;

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

        $query->where('show_on_rvtrader', self::SHOWN_ON_RVTRADER)
            ->whereNotIn('status', Inventory::UNAVAILABLE_STATUSES);

        return $query->count('inventory_id');
    }
}
