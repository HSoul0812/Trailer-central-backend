<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Exceptions\Marketing\Craigslist\InvalidDealerIdException;
use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\ActivePost;
use App\Models\Marketing\Craigslist\Queue;
use App\Models\Marketing\Craigslist\Session;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;

class SchedulerRepository implements SchedulerRepositoryInterface
{
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

    public const DEFAULT_SLOT = 99;

    /**
     * Get the records for the scheduler
     *
     * @param $params
     *
     * @throws InvalidDealerIdException
     *
     * @return DBCollection
     */
    public function scheduler($params): DBCollection
    {
        // Dealer id is always required
        if (empty($params['dealer_id'])) {
            throw new InvalidDealerIdException();
        }

        if (empty($params['slot_id'])) {
            $params['slot_id'] = self::DEFAULT_SLOT;
        }

        // Start the query
        $query = Queue::where('queue_id', '>', '0');

        // Get the needed fields
        /*$query->select(
            Session::getTableName(). '.session_id',
            Session::getTableName() . '.session_scheduled',
            Session::getTableName() . '.session_started',
            Session::getTableName() . '.notify_error_init',
            Session::getTableName() . '.notify_error_timeout',
            Queue::getTableName() . '.queue_id',
            Queue::getTableName() . '.time',
            Queue::getTableName() . '.command',
            Queue::getTableName() . '.parameter',
            Queue::getTableName() . '.inventory_id',
            Queue::getTableName() . '.status',
            Queue::getTableName() . '.state',
            Session::getTableName() . '.status AS s_status',
            Session::getTableName() . '.state AS s_state',
            Session::getTableName() . '.text_status',
            'postEdit.status as q_status',
            'postDelete.status as d_status'
        );*/

        // Conditions on the join
        $query->leftJoin(Session::getTableName(), function ($join) {
            $join->on(Session::getTableName() . '.session_id', '=', Queue::getTableName() . '.session_id');
            $join->on(Session::getTableName() . '.session_dealer_id', '=', Queue::getTableName() . '.dealer_id');
            $join->on(Session::getTableName() . '.session_profile_id', '=', Queue::getTableName() . '.profile_id');
        });

        // Join posts with postEdit
        $postEditQuery = Queue::select('status', 'parent_id', 'command')
            ->whereNotNull('parent_id')
            ->where('command', '=', 'postEdit')
            ->where('status', '<>', 'done')
            ->where('status', '<>', 'error');

        $query->leftJoinSub($postEditQuery, 'postEdit', function ($join) {
            $join->on(Queue::getTableName().'.queue_id', '=', 'postEdit.parent_id');
        });

        // Join posts with postDelete
        $postDeleteQuery = Queue::select('status', 'parent_id', 'command')
            ->whereNotNull('parent_id')
            ->where('command', '=', 'postDelete')
            ->where('status', '<>', 'error');

        $query->leftJoinSub($postDeleteQuery, 'postDelete', function ($join) {
            $join->on(Queue::getTableName().'.queue_id', '=', 'postDelete.parent_id');
        });

        // Last conditions
        $query->where(Session::getTableName() . '.session_dealer_id', '=', $params['dealer_id']);
        $query->where(Session::getTableName() . '.session_profile_id', '=', $params['profile_id']);
        $query->where(Session::getTableName() . '.session_slot_id', '=', $params['slot_id']);
        $query->where(Queue::getTableName() . '.command', '<>', 'directEdit');
        $query->where(Queue::getTableName() . '.command', '<>', 'postEdit');
        $query->whereNotNull(Session::getTableName() . '.session_scheduled');

        // Limit within a certain range of dates
        if (isset($params['start'])) {
            $query->whereDate(Session::getTableName() . '.session_scheduled', '>=', $params['start']);
        }
        if (isset($params['end'])) {
            $query->whereDate(Session::getTableName() . '.session_scheduled', '<=', $params['end']);
        }

        // If no dates are received, set a default
        if (!isset($params['start']) && !isset($params['end'])) {
            $query->whereDate(Session::getTableName() . '.session_scheduled', '>=', 'LAST_DAY(CURRENT_DATE) + INTERVAL 1 DAY - INTERVAL 2 MONTH');
        }

        // Group by session
        $query->groupBy(Session::getTableName() . '.session_id');

        // Make sure it also includes inventories with images
        $query->has('inventory')->with('inventory')->with('inventory.orderedImages');

        // Also order chronologically
        $query->orderBy(Session::getTableName() . '.session_scheduled');

        return $query->get();
    }

    /**
     * Create Facebook Page
     *
     * @param array $params
     * @return ActivePost
     */
    public function create($params)
    {
        // Create Active Post
        return ActivePost::create($params);
    }

    /**
     * Delete Page
     *
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id)
    {
        throw new NotImplementedException;
    }

    /**
     * Get Active Post
     *
     * @param array $params
     * @return ActivePost
     */
    public function get($params): ActivePost
    {
        // CLID Exists?
        if (isset($params['clid']) && $params['clid']) {
            return ActivePost::where('clid', $params['clid'])->firstOrFail();
        }

        // Find Page By ID
        return ActivePost::findOrFail($params['id']);
    }

    /**
     * Get All Scheduled Posts in Set Range
     *
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getAll($params)
    {
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
            if(!is_array($params['s_status'])) {
                $params['s_status'] = array($params['s_status']);
            }
            $query = $query->whereIn(Session::getTableName().'.status', $params['s_status']);
        }

        if (isset($params['s_status_not'])) {
            $query = $query->whereNotIn(Session::getTableName().'.status', $params['s_status_not']);
        }

        if(isset($params['q_status'])) {
            if(!is_array($params['q_status'])) {
                $params['q_status'] = array($params['q_status']);
            }
            $query = $query->whereIn(Queue::getTableName().'.status', $params['q_status']);
        }

        if (isset($params['q_status_not'])) {
            $query = $query->whereNotIn(Queue::getTableName().'.status', $params['q_status_not']);
        }

        // Limit within a certain range of dates
        if (isset($params['start'])) {
            $query->whereDate(Session::getTableName() . '.session_scheduled', '>=', $params['start']);
        }
        if (isset($params['end'])) {
            $query->whereDate(Session::getTableName() . '.session_scheduled', '<=', $params['end']);
        }

        if (!isset($params['sort'])) {
            $params['sort'] = '-scheduled';
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->has('inventory')->with('inventory')->with('inventory.orderedImages')
                     ->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Page
     *
     * @param array $params
     * @return ActivePost
     */
    public function update($params)
    {
        $post = $this->get($params);

        DB::transaction(function () use (&$post, $params) {
            // Fill Active Post Details
            $post->fill($params)->save();
        });

        return $post;
    }

    protected function getSortOrders(): array
    {
        return $this->sortOrders;
    }


    /**
     * Get Upcoming Scheduler Posts
     *
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getUpcoming(array $params): LengthAwarePaginator
    {
        // Append Status Restrictions
        $params['s_status'] = 'scheduled';
        $params['q_status_not'] = ['error', 'done'];

        // Restrict Per Page Limit
        if (!isset($params['per_page'])) {
            $params['per_page'] = 5;
        }

        // Return Special Formatted
        return $this->getAll($params);
    }

    /**
     * Get All Scheduled Posts Now Ready
     *
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getReady(array $params): LengthAwarePaginator {
        // Append Status Restrictions
        $params['s_status'] = ['scheduled', 'new'];
        $params['s_status_not'] = ['error', 'done'];
        $params['q_status_not'] = ['error', 'done'];

        // Only Get Slot 99
        $params['slot_id'] = 99;

        // Scheduled End
        $params['end'] = DB::raw('NOW()');

        // Restrict Per Page Limit
        if (!isset($params['per_page'])) {
            $params['per_page'] = 10;
        }

        // Return Special Formatted
        return $this->getAll($params);
    }

    /**
     * Get All Queued Updated Posts Now Ready
     *
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getUpdates(array $params): LengthAwarePaginator {
        // Append Status Restrictions
        $params['s_status'] = ['queued', 'new'];
        $params['s_status_not'] = ['error', 'done'];
        $params['q_status_not'] = ['error', 'done'];

        // Only Get Slot 97
        $params['slot_id'] = 97;

        // Scheduled End
        $params['end'] = DB::raw('NOW()');

        // Restrict Per Page Limit
        if (!isset($params['per_page'])) {
            $params['per_page'] = 10;
        }

        // Return Special Formatted
        return $this->getAll($params);
    }
}
