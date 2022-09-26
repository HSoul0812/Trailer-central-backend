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
use App\Services\Marketing\Craigslist\DTOs\ClappInventory;
use App\Traits\Repository\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

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
        'queue_id' => [
            'field' => 'clapp_queue.queue_id',
            'direction' => 'DESC'
        ],
        '-queue_id' => [
            'field' => 'clapp_queue.queue_id',
            'direction' => 'ASC'
        ],
        'last_posted' => [
            'field' => 'clapp_posts.added',
            'direction' => 'DESC'
        ],
        '-last_posted' => [
            'field' => 'clapp_posts.added',
            'direction' => 'ASC'
        ],
        'next_scheduled' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'DESC'
        ],
        '-next_scheduled' => [
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

        return $this->createCollection($query, $params['type'] ?? null);
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }

    /**
     * @param array $params
     *
     * @return Builder
     */
    private function buildInventoryQuery(array $params): Builder {
        /** @var Builder $query */
        $query = $this->initInventoryResultsQuery()
                      ->where(Inventory::getTableName().'.dealer_id', '=', $params['dealer_id'])
                      ->where(Profile::getTableName().'.id', '=', $params['profile_id'])
                      ->groupBy(Inventory::getTableName().'.inventory_id');

        if (isset($params['images_greater_than']) || isset($params['images_less_than'])) {
            $query = $query->selectRaw('count('.InventoryImage::getTableName().'.inventory_id) as image_count');
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where(Inventory::getTableName().'.dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['type']) && $params['type'] === 'archives') {
            $query = $this->archivedInventoryQuery($query);
        } else {
            // Skip Archived
            $query->where(function ($query) {
                $query->where(Inventory::getTableName().'.is_archived', '<>', 1)
                      ->orWhereNull(Inventory::getTableName().'.is_archived');
            });

            // Override Inventory Query
            $query = $this->overrideInventoryQuery($query, $params['dealer_id']);
        }

        $query = $this->filterInventoryQuery($query, $params);

        if (empty($params['sort'])) {
            $params['sort'] = 'created_at';
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query;
    }

    private function initInventoryResultsQuery() : Builder
    {
        return $this->initInventoryQuery()->select([
                    Inventory::getTableName().'.inventory_id', Inventory::getTableName().'.stock',
                    Inventory::getTableName().'.dealer_location_id', Inventory::getTableName().'.title',
                    Inventory::getTableName().'.category', Inventory::getTableName().'.manufacturer',
                    Inventory::getTableName().'.price', Post::getTableName().'.cl_status',
                    Image::getTableName().'.filename as primary_image', 'img2.filename as primary_image_backup',
                    Post::getTableName().'.added', Session::getTableName().'.session_scheduled',
                    Queue::getTableName().'.queue_id', Post::getTableName().'.clid',
                    Post::getTableName().'.view_url', Post::getTableName().'.manage_url'
                ]);
    }

    private function initInventoryQuery() : Builder
    {
        return DB::table(Inventory::getTableName())
                ->leftJoin(InventoryImage::getTableName(), function($join) {
                    $join->on(Inventory::getTableName().'.inventory_id', '=', InventoryImage::getTableName().'.inventory_id');
                    $join->on(function($join) {
                        $join->on(InventoryImage::getTableName().'.is_default', '=', DB::raw('1'));
                        $join->orOn(InventoryImage::getTableName().'.position', '=', DB::raw('1'));
                    });
                })
                ->leftJoin(Image::getTableName(), Image::getTableName().'.image_id',
                            '=', InventoryImage::getTableName().'.image_id')
                ->leftJoin(InventoryImage::getTableName() . ' as invImg2',
                            Inventory::getTableName().'.inventory_id', '=', 'invImg2.inventory_id')
                ->leftJoin(Image::getTableName() . ' as img2', 'img2.image_id', '=', 'invImg2.image_id')
                ->crossJoin(Profile::getTableName())
                ->leftJoin(Post::getTableName(), function($query) {
                    $query->on(Inventory::getTableName().'.inventory_id', '=', Post::getTableName().'.inventory_id')
                          ->on(Profile::getTableName().'.id', '=', Post::getTableName().'.profile_id');
                })->leftJoin(Queue::getTableName(), function($query) {
                    $query->on(Inventory::getTableName().'.inventory_id', '=', Queue::getTableName().'.inventory_id')
                          ->on(Profile::getTableName().'.id', '=', Queue::getTableName().'.profile_id');
                })->leftJoin(Session::getTableName(), function($query) {
                    $query->on(Queue::getTableName().'.session_id', '=', Session::getTableName().'.session_id')
                          ->on(Queue::getTableName().'.dealer_id', '=', Session::getTableName().'.session_dealer_id')
                          ->on(Queue::getTableName().'.profile_id', '=', Session::getTableName().'.session_profile_id')
                          ->where(Queue::getTableName().'.status', '=', self::SESSION_SCHEDULED_STATUS);
                });
    }

    private function archivedInventoryQuery(Builder $query) : Builder
    {
        return $query->where(function ($query) {
            $query->where(function ($query) {
                $query->where(Inventory::getTableName().'.is_archived', '=', 1)
                      ->orWhere(Inventory::getTableName().'.show_on_website', '=', 0)
                      ->orWhere(Inventory::getTableName().'.status', '=', 2)
                      ->orWhere(Inventory::getTableName().'.status', '=', 4)
                      ->orWhere(Inventory::getTableName().'.status', '=', 5);
            })->whereNotNull(Post::getTableName().'.clid');
        });
    }

    private function overrideInventoryQuery(Builder $query, int $dealerId) : Builder
    {
        // Get Status Overrides
        $statusAll = config('marketing.cl.settings.overrides.statusAll', '');
        if(!in_array($dealerId, explode(",", $statusAll))) {
            $query = $query->where(function($query) use($dealerId) {
                $query = $query->where(Inventory::getTableName().'.status', 1);

                // Get Status On Order Overrides
                $statusOnOrder = config('marketing.cl.settings.overrides.statusAll', '');
                if(in_array($dealerId, explode(",", $statusOnOrder))) {
                    $query = $query->orWhere(Inventory::getTableName().'.status', 3);
                }
            });
        }

        // Get Show on Website Overrides
        $showOnWebsite = config('marketing.cl.settings.overrides.showOnWebsite', '');
        if(!in_array($dealerId, explode(",", $showOnWebsite))) {
            $query = $query->where(Inventory::getTableName().'.show_on_website', 1);
        }

        // Return Result
        return $query;
    }

    private function filterInventoryQuery(Builder $query, array $params) : Builder
    {
        if (isset($params['condition'])) {
            $query = $query->where(Inventory::getTableName().'.condition', $params['condition']);
        }

        if (isset($params['search_term'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where(Inventory::getTableName().'.stock', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere(Inventory::getTableName().'.title', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere(Inventory::getTableName().'.description', 'LIKE', '%' . $params['search_term'] . '%')
                  ->orWhere(Inventory::getTableName().'.vin', 'LIKE', '%' . $params['search_term'] . '%');
            });
        }

        if (isset($params['units_with_true_cost'])) {
            if ($params['units_with_true_cost'] == self::SHOW_UNITS_WITH_TRUE_COST) {
                $query = $query->where('true_cost', '>', 0);
            } else if ($params['units_with_true_cost'] == self::DO_NOT_SHOW_UNITS_WITH_TRUE_COST) {
                $query = $query->where('true_cost', 0);
            }
        }

        // Return Query Builder
        return $query;
    }

    private function getResultsCountFromQuery(array $params) : int
    {
        /** @var Builder $query */
        $query = $this->initInventoryQuery()
                      ->select(DB::raw('count(DISTINCT ' . Inventory::getTableName() . '.inventory_id' . ') AS row_count'))
                      ->where(Inventory::getTableName().'.dealer_id', '=', $params['dealer_id'])
                      ->where(Profile::getTableName().'.id', '=', $params['profile_id']);

        if (isset($params['dealer_location_id'])) {
            $query = $query->where(Inventory::getTableName().'.dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['type']) && $params['type'] === 'archives') {
            $query = $this->archivedInventoryQuery($query);
        } else {
            // Skip Archived
            $query->where(function ($query) {
                $query->where(Inventory::getTableName().'.is_archived', '<>', 1)
                      ->orWhereNull(Inventory::getTableName().'.is_archived');
            });

            // Override Inventory Query
            $query = $this->overrideInventoryQuery($query, $params['dealer_id']);
        }

        $query = $this->filterInventoryQuery($query, $params);

        // Return Count
        return $query->first()->row_count;
    }

    private function getPaginatedResults($params)
    {
        $perPage = !isset($params['per_page']) ? self::DEFAULT_PAGE_SIZE : (int)$params['per_page'];
        $currentPage = !isset($params['page']) ? 1 : (int)$params['page'];

        $resultsCount = $this->getResultsCountFromQuery($params);

        $paginatedQuery = $this->buildInventoryQuery($params);
        if((int) $params['per_page'] !== -1) {
            $paginatedQuery->skip(($currentPage - 1) * $perPage);
            $paginatedQuery->take($perPage);
        }

        return (new LengthAwarePaginator(
            $this->createCollection($paginatedQuery, $params['type'] ?? null),
            $resultsCount,
            $perPage,
            $currentPage,
            ["path" => URL::to('/')."/api/marketing/clapp/inventory"]
        ))->appends($params);
    }

    private function createCollection(Builder $query, ?string $type): Collection {
        // Create Collection
        $collection = new Collection();
        foreach($query->get() as $item) {
            $collection->push(ClappInventory::fill($item, $type));
        }

        // Return Collection
        return $collection;
    }
}
