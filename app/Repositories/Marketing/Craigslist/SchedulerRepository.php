<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Session;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;

class SchedulerRepository implements SchedulerRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'scheduled' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'DESC'
        ],
        '-scheduled' => [
            'field' => 'clapp_session.session_scheduled',
            'direction' => 'ASC'
        ],
        'added' => [
            'field' => 'clapp_active_post.added',
            'direction' => 'DESC'
        ],
        '-added' => [
            'field' => 'clapp_active_post.added',
            'direction' => 'ASC'
        ],
        'updated' => [
            'field' => 'clapp_active_post.updated',
            'direction' => 'DESC'
        ],
        '-updated' => [
            'field' => 'clapp_active_post.updated',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Page
     * 
     * @param array $params
     * @return ActivePost
     */
    public function create($params) {
        // Create Active Post
        return ActivePost::create($params);
    }

    /**
     * Delete Page
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        throw NotImplementedException;
    }

    /**
     * Get Active Post
     * 
     * @param array $params
     * @return ActivePost
     */
    public function get($params) {
        // CLID Exists?
        if(isset($params['clid']) && $params['clid']) {
            return ActivePost::where('clid', $params['clid'])->firstOrFail();
        }

        // Find Page By ID
        return ActivePost::findOrFail($params['id']);
    }

    /**
     * Get All Scheduled Posts in Set Range
     * 
     * @param array $params
     * @return Collection<Queue>
     */
    public function getAll($params) {
        $query = Queue::leftJoin(Session::getTableName(), function (JoinClause $join) {
                    $join->on(Queue::getTableName().'.session_id', '=', Session::getTableName().'.session_id')
                         ->on(Queue::getTableName().'.dealer_id', '=', Session::getTableName().'.session_dealer_id')
                         ->on(Queue::getTableName().'.profile_id', '=', Session::getTableName().'.session_profile_id');
                })->where(Session::getTableName().'.session_dealer_id', $params['dealer_id'])
                  ->whereNotNull(Session::getTableName().'.session_scheduled');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 10000;
        }

        if (isset($params['profile_id'])) {
            $query = $query->where(Queue::getTableName().'.profile_id', $params['profile_id']);
        }

        if (isset($params['slot_id'])) {
            $query = $query->where(Session::getTableName().'.session_slot_id', $params['slot_id']);
        }

        if(isset($params['s_status'])) {
            $query = $query->where(Session::getTableName().'.status', $params['s_status']);
        }

        if(isset($params['s_status_not'])) {
            $query = $query->whereNotIn(Session::getTableName().'.status', $params['s_status_not']);
        }

        if(isset($params['q_status'])) {
            $query = $query->where(Queue::getTableName().'.status', $params['q_status']);
        }

        if(isset($params['q_status_not'])) {
            $query = $query->whereNotIn(Session::getTableName().'.status', $params['q_status_not']);
        }

        if(!isset($params['sort'])) {
            $params['sort'] = '-scheduled';
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->with('inventory')->with('inventory.primaryImage')
                     ->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Page
     * 
     * @param array $params
     * @return ActivePost
     */
    public function update($params) {
        $post = $this->get($params);

        DB::transaction(function() use (&$post, $params) {
            // Fill Active Post Details
            $post->fill($params)->save();
        });

        return $post;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }


    /**
     * Get Upcoming Scheduler Posts
     * 
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getUpcoming(array $params): LengthAwarePaginator {
        // Append Status Restrictions
        $params['s_status'] = 'scheduled';
        $params['q_status'] = 'done';
        $params['q_status_not'] = ['error', 'done'];

        // Restrict Per Page Limit
        if (!isset($params['per_page'])) {
            $params['per_page'] = 5;
        }

        // Return Special Formatted
        return $this->getAll($params);
    }
}