<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Inventory\Inventory;
use App\Models\Marketing\Craigslist\ActivePost;
use App\Repositories\Traits\SortTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class InventoryRepository
 * @package App\Repositories\Marketing\Craigslist
 */
class InventoryRepository implements InventoryRepositoryInterface
{
    use SortTrait, Transaction;

    private const DEFAULT_PAGE_SIZE = 15;


    private $sortOrders = [
        'inventory_id' => [
            'field' => 'inventory.inventory_id',
            'direction' => 'DESC'
        ],
        '-inventory_id' => [
            'field' => 'inventory.inventory_id',
            'direction' => 'ASC'
        ],
        'title' => [
            'field' => 'inventory.title',
            'direction' => 'DESC'
        ],
        '-title' => [
            'field' => 'inventory.title',
            'direction' => 'ASC'
        ],
        'stock' => [
            'field' => 'inventory.stock',
            'direction' => 'DESC'
        ],
        '-stock' => [
            'field' => 'inventory.stock',
            'direction' => 'ASC'
        ],
        'category' => [
            'field' => 'inventory.category',
            'direction' => 'DESC'
        ],
        '-category' => [
            'field' => 'inventory.category',
            'direction' => 'ASC'
        ],
        'manufacturer' => [
            'field' => 'inventory.manufacturer',
            'direction' => 'DESC'
        ],
        '-manufacturer' => [
            'field' => 'inventory.manufacturer',
            'direction' => 'ASC'
        ],
        'price' => [
            'field' => 'inventory.price',
            'direction' => 'DESC'
        ],
        '-price' => [
            'field' => 'inventory.price',
            'direction' => 'ASC'
        ],
        'status' => [
            'field' => 'clapp_posts.status',
            'direction' => 'DESC'
        ],
        '-status' => [
            'field' => 'clapp_posts.status',
            'direction' => 'ASC'
        ],
        'posted_at' => [
            'field' => 'clapp_posts.added',
            'direction' => 'DESC'
        ],
        '-posted_at' => [
            'field' => 'clapp_posts.added',
            'direction' => 'ASC'
        ],
        'scheduled_at' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'DESC'
        ],
        '-scheduled_at' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'inventory.created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'inventory.created_at',
            'direction' => 'ASC'
        ],
        'updated_at' => [
            'field' => 'inventory.updated_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'inventory.updated_at',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param array $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @param array $options
     *
     * @throws NotImplementedException
     */
    public function update($params, array $options = [])
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * 
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * 
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @param bool $withDefault
     * @param bool $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $withDefault = true, bool $paginated = false)
    {
        if ($paginated) {
            return $this->getPaginatedResults($params, $withDefault);
        }

        $query = $this->buildInventoryQuery($params, $withDefault);

        return $query->get();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }

    /**
     * @param array $params
     * @param bool $withDefault whether to apply default conditions or not
     *
     * @return Builder
     */
    private function buildInventoryQuery(
        array $params,
        bool $withDefault = true,
        array $select = ['inventory.*']
    ) : GrimzyBuilder {
        /** @var Builder $query */
        $query = Inventory::query()->select($select)
            ->crossJoin(Profile::getTableName())
            ->leftJoin(Post::getTableName(), function($query) {
                $query->where(Inventory::getTableName().'.inventory_id', '=', Post::getTableName().'.inventory_id')
                      ->where(Profile::getTableName().'.profile_id', '=', Post::getTableName().'.profile_id');
            })
            ->leftJoin(ActivePost::getTableName(), function($query) {
                $query->where(Inventory::getTableName().'.inventory_id', '=', ActivePost::getTableName().'.inventory_id')
                      ->where(Profile::getTableName().'.profile_id', '=', ActivePost::getTableName().'.profile_id');
            })
            ->where(Inventory::getTableName().'.dealer_id', '=', $params['dealer_id'])
            ->where(Profile::getTableName().'.profile_id', '=', $params['profile_id'])
            /*->where(function ($query) {
                $query->where(function ($query) {
                    $query->where(Inventory::getTableName().'.is_archived', '=', 1)
                          ->orWhere(Inventory::getTableName().'.show_on_website', '=', 0);
                })->where();
            })*/
            ->with('orderedImages');

        if (isset($params['include']) && is_string($params['include'])) {
            $query = $query->with(explode(',', $params['include']));
        }

        if ($withDefault) {
            $query = $query->where('status', '<>', Inventory::STATUS_QUOTE);
        }

        // Get Status Overrides
        $statusAll = config('marketing.cl.overrides.statusAll', '');
        if(in_array($params['dealer_id'], explode(",", $statusAll))) {
            $query = $query->where(function($query) {
                $query = $query->where('inventory.status', 1);

                // Get Status On Order Overrides
                $statusOnOrder = config('marketing.cl.overrides.statusAll', '');
                if(in_array($params['dealer_id'], explode(",", $statusOnOrder))) {
                    $query = $query->orWhere('inventory.status', 3);
                }
            });
        }

        // Get Show on Website Overrides
        $showOnWebsite = config('marketing.cl.overrides.showOnWebsite', '');
        if(in_array($params['dealer_id'], explode(",", $showOnWebsite))) {
            $query = $query->where('inventory.show_on_website', 1);
        }

        if (isset($params['condition'])) {
            $query = $query->where('condition', $params['condition']);
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('inventory.dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['is_archived'])) {
            $withDefault = false;
            $query = $query->where('inventory.is_archived', $params['is_archived']);
        }

        if (isset($params['search_term'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where(Inventory::getTableName().'.stock', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere(Inventory::getTableName().'.title', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere(Inventory::getTableName().'.description', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere(Inventory::getTableName().'.vin', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhereHas('floorplanVendor', function ($query) use ($params) {
                    $query->where('name', 'LIKE', '%' . $params['search_term'] . '%');
                  });
            });
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        if (isset($params['images_greater_than']) || isset($params['images_less_than'])) {
            $query = $query->leftJoin('inventory_image', 'inventory_image.inventory_id', '=', 'inventory.inventory_id');
            $query->selectRaw('count(inventory_image.inventory_id) as image_count');
            $query->groupBy('inventory.inventory_id');
        }

        return $query;
    }

    private function getResultsCountFromQuery(GrimzyBuilder $query) : int
    {
        $queryString = str_replace(array('?'), array('\'%s\''), $query->toSql());
        $queryString = vsprintf($queryString, $query->getBindings());
        return current(DB::select(DB::raw("SELECT count(*) as row_count FROM ($queryString) as inventory_count")))->row_count;
    }

    private function getPaginatedResults($params, bool $withDefault = true)
    {
        $perPage = !isset($params['per_page']) ? self::DEFAULT_PAGE_SIZE : (int)$params['per_page'];
        $currentPage = !isset($params['page']) ? 1 : (int)$params['page'];

        $paginatedQuery = $this->buildInventoryQuery($params, $withDefault);
        $resultsCount = $this->getResultsCountFromQuery($paginatedQuery);

        $paginatedQuery->skip(($currentPage - 1) * $perPage);
        $paginatedQuery->take($perPage);

        return (new LengthAwarePaginator(
            $paginatedQuery->get(),
            $resultsCount,
            $perPage,
            $currentPage,
            ["path" => URL::to('/')."/api/inventory"]
        ))->appends($params);
    }
}
