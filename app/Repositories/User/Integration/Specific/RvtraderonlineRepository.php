<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration\Specific;

use App\Models\Inventory\Inventory;

class RvtraderonlineRepository implements SpecificIntegrationRepositoryInterface
{
    /**
     * @var Inventory
     */
    private $model;

    public function __construct(Inventory $model)
    {
        $this->model = $model;
    }

    public const SHOWN_ON_RVTRADER = 1;

    public function get(array $params): array
    {
        $query = $this->model::query();

        if (!empty($params['dealer_id'])) {
            $query->where('dealer_id', $params['dealer_id']);
        }

        $query->where('show_on_rvtrader', self::SHOWN_ON_RVTRADER)
            ->whereNotIn('status', Inventory::UNAVAILABLE_STATUSES);

        return [
            'package' => $query->count('inventory_id') // aka used slots
        ];
    }
}
