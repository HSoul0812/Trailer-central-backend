<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Exceptions\NotImplementedException;
use App\Exceptions\OperationNotAllowedException;
use App\Models\Inventory\InventoryHistory;
use App\Repositories\Traits\SortTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryHistoryRepository implements InventoryHistoryRepositoryInterface
{
    use SortTrait;

    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'type' => [
            'field' => 'type',
            'direction' => 'DESC'
        ],
        '-type' => [
            'field' => 'type',
            'direction' => 'ASC'
        ],
        'subtype' => [
            'field' => 'subtype',
            'direction' => 'DESC'
        ],
        '-subtype' => [
            'field' => 'subtype',
            'direction' => 'ASC'
        ],
        'customer_name' => [
            'field' => 'customer_name',
            'direction' => 'DESC'
        ],
        '-customer_name' => [
            'field' => 'customer_name',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param  array  $params
     * @param  bool  $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false)
    {
        $query = InventoryHistory::select('*');

        $params['per_page'] = $params['per_page'] ?? 15;

        if (isset($params['customer_id'])) {
            $query->where('customer_id', $params['customer_id']);
        }

        if (isset($params['inventory_id'])) {
            $query->where('inventory_id', $params['inventory_id']);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (isset($params['search_term'])) {
            $term = $params['search_term'];

            $query->where(static function ($subQuery) use ($term): void {
                $subQuery->where('customer_name', 'LIKE', "%{$term}%")
                    ->orWhere('type', 'LIKE', "%{$term}%")
                    ->orWhere('subtype', 'LIKE', "%{$term}%")
                    ->orWhere('vin', 'LIKE', "%{$term}%")
                    ->orWhere('text_1', 'LIKE', "%{$term}%")
                    ->orWhere('text_2', 'LIKE', "%{$term}%")
                    ->orWhere('text_3', 'LIKE', "%{$term}%");
            });
        }

        if (isset($params['sort'])) {
            $this->addSortQuery($query, $params['sort']);
        }

        return $paginated ? $query->paginate($params['per_page'])->appends($params) : $query->get();
    }

    /**
     * @param $params
     * @return InventoryHistory
     *
     * @throws OperationNotAllowedException
     */
    public function create($params): InventoryHistory
    {
        throw new OperationNotAllowedException();
    }

    /**
     * @param array $params
     * @return InventoryHistory
     *
     * @throws OperationNotAllowedException
     */
    public function update($params): InventoryHistory
    {
        throw new OperationNotAllowedException();
    }

    /**
     * @param  array $params
     * @return InventoryHistory
     */
    public function get($params): InventoryHistory
    {
        throw new NotImplementedException('Not implemented yet.');
    }

    /**
     * @param array $params
     * @return boolean
     *
     * @throws OperationNotAllowedException
     */
    public function delete($params): bool
    {
        throw new OperationNotAllowedException();
    }

    protected function getSortOrders(): array
    {
        return $this->sortOrders;
    }
}
