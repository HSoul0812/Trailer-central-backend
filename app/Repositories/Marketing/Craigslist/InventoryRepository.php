<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use App\Models\Inventory\Image;
use App\Models\Marketing\Craigslist\Post;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Session;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class InventoryRepository
 * @package App\Repositories\Marketing\Craigslist
 */
class InventoryRepository implements InventoryRepositoryInterface
{
    use SortTrait, Transaction;

    private const DEFAULT_PAGE_SIZE = 15;

    private const SHOW_UNITS_WITH_TRUE_COST = 1;
    private const DO_NOT_SHOW_UNITS_WITH_TRUE_COST = 0;

    private const SESSION_SCHEDULED_STATUS = 'scheduled';


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
     * @param bool $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false)
    {
        if ($paginated) {
            return $this->getPaginatedResults($params);
        }

        $query = $this->buildInventoryQuery($params);

        return $this->createCollection($query);
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }

    /**
     * @param array $params
     *
     * @return Builder
     */
    private function buildInventoryQuery(array $params): GrimzyBuilder {
        /** @var Builder $query */
        $query = $this->initInventoryQuery()
                      ->where(Inventory::getTableName().'.dealer_id', '=', $params['dealer_id'])
                      ->where(Profile::getTableName().'.profile_id', '=', $params['profile_id']);

        if (isset($params['include']) && is_string($params['include'])) {
            $query = $query->with(explode(',', $params['include']));
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('inventory.dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['type']) && $params['type'] === 'archives') {
            $query = $this->archivedInventoryQuery($query);
        } else {
            $query = $this->overrideInventoryQuery($query, $params['dealer_id']);
        }

        $query = $this->filterInventoryQuery($query, $params);

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query;
    }

    private function initInventoryQuery() : GrimzyBuilder
    {
        return DB::table(Inventory::getTableName())->select([
                    Inventory::getTableName().'.inventory_id', Inventory::getTableName().'.stock',
                    Inventory::getTableName().'.title', Inventory::getTableName().'.category',
                    Inventory::getTableName().'.manufacturer', Inventory::getTableName().'.price',
                    Post::getTableName().'.cl_status as status',
                    Image::getTableName().'.filename as primary_image',
                    Post::getTableName().'.added as last_posted',
                    Session::getTableName().'.session_scheduled as next_scheduled',
                    Queue::getTableName().'.queue_id', Post::getTableName().'.clid',
                    Post::getTableName().'.view_url', Post::getTableName().'.manage_url'
                ])->leftJoin(InventoryImage::getTableName(),Image::getTableName().'.inventory_id',
                            '=', InventoryImage::getTableName().'.inventory_id')
                ->leftJoin(Image::getTableName(), Image::getTableName().'.image_id',
                            '=', InventoryImage::getTableName().'.image_id')
                ->crossJoin(Profile::getTableName())
                ->leftJoin(Post::getTableName(), function($query) {
                    $query->where(Inventory::getTableName().'.inventory_id', '=', Post::getTableName().'.inventory_id')
                          ->where(Profile::getTableName().'.profile_id', '=', Post::getTableName().'.profile_id');
                })->leftJoin(Post::getTableName(), function($query) {
                    $query->where(Inventory::getTableName().'.inventory_id', '=', Post::getTableName().'.inventory_id')
                          ->where(Profile::getTableName().'.profile_id', '=', Post::getTableName().'.profile_id');
                })->leftJoin(Queue::getTableName(), function($query) {
                    $query->where(Inventory::getTableName().'.inventory_id', '=', Queue::getTableName().'.inventory_id')
                          ->where(Profile::getTableName().'.id', '=', Queue::getTableName().'.profile_id');
                })->leftJoin(Session::getTableName(), function($query) {
                    $query->where(Queue::getTableName().'.session_id', '=', Session::getTableName().'.session_id')
                          ->where(Queue::getTableName().'.dealer_id', '=', Session::getTableName().'.session_dealer_id')
                          ->where(Queue::getTableName().'.profile_id', '=', Session::getTableName().'.session_profile_id')
                          ->where(Queue::getTableName().'.status', '=', self::SESSION_SCHEDULED_STATUS);
                });
    }

    private function archivedInventoryQuery(GrimzyBuilder $query) : GrimzyBuilder
    {
        return $query->where(function ($query) {
            $query->where(function ($query) {
                $query->where(Inventory::getTableName().'.is_archived', '=', 1)
                      ->orWhere(Inventory::getTableName().'.show_on_website', '=', 0)
                      ->orWhere(Inventory::getTableName().'.status', '=', 2)
                      ->orWhere(Inventory::getTableName().'.status', '=', 4)
                      ->orWhere(Inventory::getTableName().'.status', '=', 5);
            })->where(Inventory::getTableName().'.');
        });
    }

    private function overrideInventoryQuery(GrimzyBuilder $query, int $dealerId) : GrimzyBuilder
    {
        // Get Status Overrides
        $statusAll = config('marketing.cl.overrides.statusAll', '');
        if(in_array($dealerId, explode(",", $statusAll))) {
            $query = $query->where(function($query) {
                $query = $query->where('inventory.status', 1);

                // Get Status On Order Overrides
                $statusOnOrder = config('marketing.cl.overrides.statusAll', '');
                if(in_array($dealerId, explode(",", $statusOnOrder))) {
                    $query = $query->orWhere('inventory.status', 3);
                }
            });
        }

        // Get Show on Website Overrides
        $showOnWebsite = config('marketing.cl.overrides.showOnWebsite', '');
        if(in_array($dealerId, explode(",", $showOnWebsite))) {
            $query = $query->where('inventory.show_on_website', 1);
        }

        // Return Result
        return $query;
    }

    private function filterInventoryQuery(GrimzyBuilder $query, array $params) : GrimzyBuilder
    {
        if (isset($params['condition'])) {
            $query = $query->where('condition', $params['condition']);
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

        if (isset($params['units_with_true_cost'])) {
            if ($params['units_with_true_cost'] == self::SHOW_UNITS_WITH_TRUE_COST) {
                $query = $query->where('true_cost', '>', 0);
            } else if ($params['units_with_true_cost'] == self::DO_NOT_SHOW_UNITS_WITH_TRUE_COST) {
                $query = $query->where('true_cost', 0);
            }
        }

        if (isset($params['images_greater_than']) || isset($params['images_less_than'])) {
            $query = $query->leftJoin('inventory_image', 'inventory_image.inventory_id', '=', 'inventory.inventory_id');
            $query->selectRaw('count(inventory_image.inventory_id) as image_count');
            $query->groupBy('inventory.inventory_id');
        }

        // Return Query Builder
        return $query;
    }

    private function getResultsCountFromQuery(GrimzyBuilder $query) : int
    {
        $queryString = str_replace(array('?'), array('\'%s\''), $query->toSql());
        $queryString = vsprintf($queryString, $query->getBindings());
        return current(DB::select(DB::raw("SELECT count(*) as row_count FROM ($queryString) as inventory_count")))->row_count;
    }

    private function getPaginatedResults($params)
    {
        $perPage = !isset($params['per_page']) ? self::DEFAULT_PAGE_SIZE : (int)$params['per_page'];
        $currentPage = !isset($params['page']) ? 1 : (int)$params['page'];

        $paginatedQuery = $this->buildInventoryQuery($params);
        $resultsCount = $this->getResultsCountFromQuery($paginatedQuery);

        $paginatedQuery->skip(($currentPage - 1) * $perPage);
        $paginatedQuery->take($perPage);

        return (new LengthAwarePaginator(
            $this->createCollection($paginatedQuery),
            $resultsCount,
            $perPage,
            $currentPage,
            ["path" => URL::to('/')."/api/marketing/clapp/inventory"]
        ))->appends($params);
    }

    private function createCollection(GrimzyBuilder $query): Collection {
        // Create Collection
        $collection = new Collection();
        foreach($query->get() as $item) {
            $collection->push(ClappInventory::build($item));
        }

        // Return Collection
        return $collection;
    }
}
