<?php

declare(strict_types=1);

namespace App\Repositories\Dms\Customer;

use App\Models\CRM\Dms\Customer\CustomerInventory;
use App\Repositories\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Describes the repository API for customer inventory
 */
interface InventoryRepositoryInterface extends Repository
{
    public const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
            ['active', '=', 1],
            ['is_archived', '<>', 1]
        ]
    ];

    public const TENANCY_CONDITION = [
        'has' => 'has',
        'does_not_have' => '-has'
    ];

    /**
     * @param array $params
     * @param bool $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false);

    /**
     * @param array<string> $uuids  array of ID for dms_customer_inventory records
     * @return bool
     */
    public function bulkDestroy(array $uuids): bool;

    /**
     * @param int $customer_id
     * @param int $inventory_id
     */
    public function findFirstByCustomerAndInventory(int $customer_id, int $inventory_id);
}
